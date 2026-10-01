<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Donor;
use App\Models\PaymentTransaction;
use App\Services\TierNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SquadPaymentController extends Controller
{
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.squad.secret_key');
        $this->baseUrl   = rtrim(config('services.squad.base_url', 'https://api-d.squadco.com'), '/');
    }

    /**
     * Initiate a Squad payment and return a checkout URL.
     * POST /api/squad/pay
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'amount'        => 'required|numeric|min:100',
            'email'         => 'required|email',
            'customer_name' => 'nullable|string|max:255',
            'name'          => 'nullable|string|max:255',
            'callback_url'  => 'nullable|string',
            'project_id'    => 'nullable|integer|exists:projects,id',
            'donor_id'      => 'nullable|integer',
            'project_title' => 'nullable|string',
        ]);

        if (empty($this->secretKey)) {
            Log::error('Squad payment initiation failed: SQUAD_SECRET_KEY not configured');
            return response()->json([
                'message' => 'Payment provider configuration is incomplete. Please contact support.',
            ], 500);
        }

        $amountNaira  = (float) $request->input('amount');
        $amountKobo   = (int) round($amountNaira * 100);
        $email        = $request->input('email');
        $customerName = trim($request->input('customer_name', $request->input('name', '')));
        $projectId    = $request->input('project_id');
        $donorId      = $request->input('donor_id');

        // Find or create donor by email
        $donor = Donor::firstOrCreate(
            ['email' => $email],
            [
                'name'       => $customerName ?: 'Anonymous',
                'surname'    => '',
                'donor_type' => 'addressable_alumni',
            ]
        );

        // Create donation record before calling Squad
        $donation = Donation::create([
            'donor_id'          => $donorId ?: $donor->id,
            'project_id'        => $projectId,
            'amount'            => $amountNaira,
            'type'              => $projectId ? 'project' : 'endowment',
            'frequency'         => 'onetime',
            'endowment'         => $projectId ? 'no' : 'yes',
            'status'            => 'pending',
            'payment_reference' => 'ABU_SQUAD_' . time() . '_' . uniqid(),
        ]);

        // Track initialization event
        $initTransaction = PaymentTransaction::updateOrCreate(
            [
                'payment_gateway'   => 'squad',
                'payment_reference' => $donation->payment_reference,
            ],
            [
                'donation_id'       => $donation->id,
                'donor_id'          => $donorId ?: $donor->id,
                'project_id'        => $projectId,
                'category'          => $projectId ? 'project' : 'general',
                'event_type'        => 'payment.initialized',
                'gateway_reference' => null,
                'amount'            => $amountNaira,
                'currency'          => 'NGN',
                'status'            => 'pending',
                'gateway_status'    => 'initialized',
                'channel'           => null,
                'fee'               => 0,
            ]
        );

        // Use custom callback if provided (e.g. Capacitor deep link)
        $callbackUrl = $request->input('callback_url') 
            ?: url('/donation/thank-you') . '?transaction_ref=' . $donation->payment_reference;

        $payload = [
            'amount'          => $amountKobo,
            'email'           => $email,
            'currency'        => 'NGN',
            'initiate_type'   => 'inline',
            'transaction_ref' => $donation->payment_reference,
            'callback_url'    => $callbackUrl,
            'metadata'        => [
                'amount_naira'  => $amountNaira,
                'customer_name' => $customerName,
                'donor_id'      => $donor->id,
                'donation_id'   => $donation->id,
                'project_id'    => $projectId,
            ],
        ];

        if ($customerName) {
            $payload['customer_name'] = $customerName;
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->timeout(30)
                ->post("{$this->baseUrl}/transaction/initiate", $payload);

            if ($response->failed()) {
                $donation->update(['status' => 'failed']);
                $initTransaction->update([
                    'status'           => 'failed',
                    'gateway_status'   => 'failed',
                    'response_payload' => json_encode($response->json()),
                ]);
                Log::error('Squad payment initiation failed', [
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                    'payload' => $payload,
                ]);
                return response()->json([
                    'message' => 'Unable to initiate payment. Please try again later.',
                    'details' => $response->json(),
                ], 500);
            }

            $data        = $response->json();
            $checkoutUrl = data_get($data, 'data.checkout_url');

            if (!$checkoutUrl) {
                $donation->update(['status' => 'failed']);
                $initTransaction->update([
                    'status'           => 'failed',
                    'gateway_status'   => 'failed',
                    'response_payload' => json_encode($data),
                ]);
                Log::error('Squad payment initiation missing checkout URL', ['response' => $data]);
                return response()->json([
                    'message' => 'Payment provider did not return a checkout URL.',
                    'details' => $data,
                ], 500);
            }

            // Update init transaction with Squad's gateway reference
            $initTransaction->update([
                'gateway_reference' => data_get($data, 'data.transaction_ref'),
                'response_payload'  => json_encode($data),
            ]);

            return response()->json([
                'checkout_url'    => $checkoutUrl,
                'transaction_ref' => $donation->payment_reference,
            ]);

        } catch (\Exception $e) {
            $donation->update(['status' => 'failed']);
            Log::error('Squad payment initiation exception', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Unexpected error while initiating payment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm a Squad payment after Squad redirects the user back.
     * GET /donation/thank-you?transaction_ref=...
     */
    public function confirm(Request $request)
    {
        $ref = $request->query('transaction_ref');

        if (!$ref) {
            return view('donation-thank-you', [
                'success'   => false,
                'donorName' => 'Guest',
                'amount'    => 0,
                'email'     => '',
                'tierName'  => null,
                'ref'       => null,
                'emailSent' => false,
            ]);
        }

        try {
            // Verify with Squad
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->timeout(30)
                ->get("{$this->baseUrl}/transaction/verify/{$ref}");

            if ($response->failed()) {
                Log::error('Squad verify failed', ['ref' => $ref, 'status' => $response->status()]);
                return $this->failedView($ref);
            }

            $data   = $response->json();
            $txData = data_get($data, 'data', []);

            // Squad status field varies; check multiple keys
            $status      = strtolower(data_get($txData, 'transaction_status', data_get($txData, 'status', '')));
            $isSuccess   = in_array($status, ['success', 'complete', 'successful', 'approved']);

            if (!$isSuccess) {
                Log::info('Squad payment not successful', ['ref' => $ref, 'status' => $status]);
                return $this->failedView($ref);
            }

            $email        = data_get($txData, 'email', '');
            $amountKobo   = (int) data_get($txData, 'transaction_amount', data_get($txData, 'amount', 0));
            $amountNaira  = $amountKobo > 0 ? $amountKobo / 100 : (float) data_get($txData, 'metadata.amount_naira', 0);
            $customerName = data_get($txData, 'customer_name', data_get($txData, 'metadata.customer_name', 'Valued Donor'));

            // Find existing donor or build a minimal name for display
            $donor = Donor::where('email', $email)->first();

            // Create donation record (idempotent)
            $donation = Donation::updateOrCreate(
                ['payment_reference' => $ref],
                [
                    'donor_id'          => $donor?->id,
                    'amount'            => $amountNaira,
                    'type'              => 'endowment',
                    'frequency'         => 'onetime',
                    'endowment'         => 'yes',
                    'status'            => 'completed',
                    'payment_reference' => $ref,
                    'verified_at'       => now(),
                    'paid_at'           => now(),
                ]
            );

            // Record transaction (idempotent — skip if charge.success already logged)
            if (!PaymentTransaction::where('payment_reference', $ref)
                    ->whereIn('event_type', ['charge.success', 'payment.completed'])
                    ->exists()) {
                PaymentTransaction::updateOrCreate(
                    [
                        'payment_gateway'   => 'squad',
                        'payment_reference' => $ref,
                    ],
                    [
                        'donation_id'       => $donation->id,
                        'donor_id'          => $donation->donor_id,
                        'project_id'        => $donation->project_id,
                        'category'          => $donation->project_id ? 'project' : 'general',
                        'event_type'        => 'charge.success',
                        'gateway_reference' => data_get($txData, 'transaction_ref', $ref),
                        'amount'            => $amountNaira,
                        'currency'          => 'NGN',
                        'status'            => 'completed',
                        'gateway_status'    => $status,
                        'channel'           => data_get($txData, 'payment_type'),
                        'fee'               => data_get($txData, 'fee', 0),
                        'response_payload'  => json_encode($data),
                    ]
                );
            }

            // Tier check + email (only if donor exists in our system)
            $emailSent = false;
            $tierName  = null;

            if ($donor) {
                try {
                    $donation->load('donor', 'project');
                    (new TierNotificationService())->handleDonationTierCheck($donation);
                    $emailSent = true;

                    // Refresh donor to get updated tier
                    $donor->refresh();
                    if ($donor->donor_tier_id) {
                        $donor->load('tier');
                        $tierName = $donor->tier?->name;
                    }
                } catch (\Exception $e) {
                    Log::error('Squad: tier/email step failed', ['ref' => $ref, 'error' => $e->getMessage()]);
                }
            } else {
                // Guest donor — send a generic thank-you email via the closest matching template
                $emailSent = $this->sendGuestThankYouEmail($email, $customerName, $amountNaira, $ref);
            }

            $displayName = $donor
                ? trim("{$donor->surname} {$donor->name}") ?: $customerName
                : $customerName;

            return view('donation-thank-you', [
                'success'   => true,
                'donorName' => $displayName ?: 'Valued Donor',
                'amount'    => $amountNaira,
                'email'     => $email,
                'tierName'  => $tierName,
                'ref'       => $ref,
                'emailSent' => $emailSent,
            ]);

        } catch (\Exception $e) {
            Log::error('Squad confirm exception', ['ref' => $ref, 'error' => $e->getMessage()]);
            return $this->failedView($ref);
        }
    }

    /**
     * API Verification for Mobile App
     * GET /api/squad/verify/{reference}
     */
    public function verifyApi($reference)
    {
        if (!$reference) {
            return response()->json(['success' => false, 'message' => 'Missing reference'], 400);
        }

        try {
            $donation = Donation::where('payment_reference', $reference)->first();
            
            if (!$donation) {
                return response()->json(['success' => false, 'message' => 'Donation not found'], 404);
            }

            if ($donation->status === 'completed') {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already verified',
                    'data' => ['status' => 'completed']
                ]);
            }

            // Verify with Squad
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->timeout(30)
                ->get("{$this->baseUrl}/transaction/verify/{$reference}");

            if ($response->failed()) {
                $donation->update(['status' => 'failed']);
                PaymentTransaction::updateOrCreate(
                    [
                        'payment_gateway'   => 'squad',
                        'payment_reference' => $reference,
                    ],
                    [
                        'donation_id'       => $donation->id,
                        'donor_id'          => $donation->donor_id,
                        'project_id'        => $donation->project_id,
                        'category'          => $donation->project_id ? 'project' : 'general',
                        'event_type'        => 'charge.failed',
                        'gateway_reference' => $reference,
                        'amount'            => $donation->amount,
                        'currency'          => 'NGN',
                        'status'            => 'failed',
                        'gateway_status'    => 'not_found',
                        'channel'           => null,
                        'fee'               => 0,
                        'response_payload'  => json_encode($response->json() ?? []),
                    ]
                );
                return response()->json(['success' => false, 'message' => 'Squad verification failed'], 400);
            }

            $data   = $response->json();
            $txData = data_get($data, 'data', []);

            $status      = strtolower(data_get($txData, 'transaction_status', data_get($txData, 'status', '')));
            $isSuccess   = in_array($status, ['success', 'complete', 'successful', 'approved']);

            if (!$isSuccess) {
                // If they cancelled, it's not a success, so we should mark it as failed to stop it from pending forever.
                $donation->update(['status' => 'failed']);
                PaymentTransaction::updateOrCreate(
                    [
                        'payment_gateway'   => 'squad',
                        'payment_reference' => $reference,
                    ],
                    [
                        'donation_id'       => $donation->id,
                        'donor_id'          => $donation->donor_id,
                        'project_id'        => $donation->project_id,
                        'category'          => $donation->project_id ? 'project' : 'general',
                        'event_type'        => 'charge.failed',
                        'gateway_reference' => data_get($txData, 'transaction_ref', $reference),
                        'amount'            => $donation->amount,
                        'currency'          => 'NGN',
                        'status'            => 'failed',
                        'gateway_status'    => $status,
                        'channel'           => null,
                        'fee'               => 0,
                        'response_payload'  => json_encode($data),
                    ]
                );
                
                return response()->json(['success' => false, 'message' => 'Payment not successful', 'data' => ['status' => $status]]);
            }

            $amountKobo   = (int) data_get($txData, 'transaction_amount', data_get($txData, 'amount', 0));
            $amountNaira  = $amountKobo > 0 ? $amountKobo / 100 : $donation->amount;

            $donation->update([
                'status'            => 'completed',
                'amount'            => $amountNaira,
                'verified_at'       => now(),
                'paid_at'           => now(),
            ]);

            PaymentTransaction::updateOrCreate(
                [
                    'payment_gateway'   => 'squad',
                    'payment_reference' => $reference,
                ],
                [
                    'donation_id'       => $donation->id,
                    'donor_id'          => $donation->donor_id,
                    'project_id'        => $donation->project_id,
                    'category'          => $donation->project_id ? 'project' : 'general',
                    'event_type'        => 'charge.success',
                    'gateway_reference' => data_get($txData, 'transaction_ref', $reference),
                    'amount'            => $amountNaira,
                    'currency'          => 'NGN',
                    'status'            => 'completed',
                    'gateway_status'    => $status,
                    'channel'           => data_get($txData, 'payment_type'),
                    'fee'               => data_get($txData, 'fee', 0),
                    'response_payload'  => json_encode($data),
                ]
            );

            if ($donation->project_id) {
                $project = \App\Models\Project::find($donation->project_id);
                if ($project) {
                    $raised = Donation::where('project_id', $donation->project_id)->where('status', 'completed')->sum('amount');
                    $project->update(['raised' => $raised]);
                }
            }

            // Send Thank You Email and Tier Check
            try {
                $donation->load('donor', 'project');
                (new TierNotificationService())->handleDonationTierCheck($donation);

                $donor = $donation->donor;
                if ($donor && $donor->email) {
                    $donorName = trim("{$donor->surname} {$donor->name}");
                    $projectName = $donation->project ? $donation->project->project_title : 'GIVE ABU';
                    
                    Mail::send('emails.thank-you', [
                        'donorName'    => $donorName ?: 'Valued Donor',
                        'amount'       => number_format($amountNaira, 2),
                        'reference'    => $reference,
                        'projectName'  => $projectName,
                        'donationDate' => $donation->paid_at ?? now(),
                        'donationType' => $donation->endowment === 'yes' ? 'GIVE ABU Fund' : 'Project Donation',
                        'logoUrl'      => 'https://abu-endowment.cloud/abu_logo_white_for_email.png',
                    ], function($message) use ($donor) {
                        $message->from(config('mail.from.address', 'noreply@abu-endowment.edu.ng'), config('mail.from.name', 'GIVE ABU'))
                                ->to($donor->email)
                                ->subject('Thank You for Your Generous Donation - GIVE ABU');
                    });
                }
            } catch (\Exception $e) {
                Log::error('Squad API verify: email failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'data' => [
                    'status' => 'completed',
                    'amount' => $amountNaira,
                    'reference' => $reference
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Squad API verify exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error during verification'], 500);
        }
    }

    /**
     * Handle Squad webhook events.
     * POST /api/squad/webhook
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Squad webhook received', $payload);

        $event          = $payload['event'] ?? $payload['Event'] ?? null;
        
        $data = $payload['data'] ?? $payload['Body'] ?? $payload;
        $transactionRef = $data['transaction_ref'] ?? $data['TransactionRef'] ?? $payload['transaction_ref'] ?? $payload['TransactionRef'] ?? null;

        if (!$transactionRef) {
            Log::warning('Squad webhook: Missing transaction_ref', ['payload' => $payload]);
            return response()->json(['message' => 'Missing transaction_ref'], 200);
        }

        $donation = Donation::where('payment_reference', $transactionRef)->first();

        if (!$donation) {
            Log::warning('Squad webhook: Donation not found', ['transaction_ref' => $transactionRef]);
            return response()->json(['message' => 'Donation not found'], 200);
        }

        if ($donation->status === 'completed') {
            return response()->json(['message' => 'Webhook already processed'], 200);
        }

        $donation->load('donor', 'project');
        
        $normalizedEvent = strtolower((string)$event);

        if (in_array($normalizedEvent, ['charge.success', 'charge_successful', 'charge.successful'])) {
            $donation->update([
                'status'      => 'completed',
                'verified_at' => now(),
                'paid_at'     => $data['paid_at'] ?? now(),
            ]);

            PaymentTransaction::updateOrCreate(
                [
                    'payment_gateway'   => 'squad',
                    'payment_reference' => $transactionRef,
                ],
                [
                    'donation_id'       => $donation->id,
                    'donor_id'          => $donation->donor_id,
                    'project_id'        => $donation->project_id,
                    'category'          => $donation->project_id ? 'project' : 'general',
                    'event_type'        => 'charge.success',
                    'gateway_reference' => $data['id'] ?? $data['transaction_ref'] ?? null,
                    'amount'            => $data['amount'] ?? $donation->amount,
                    'currency'          => 'NGN',
                    'status'            => 'completed',
                    'gateway_status'    => $data['status'] ?? 'success',
                    'channel'           => $data['channel'] ?? $data['payment_type'] ?? null,
                    'fee'               => $data['fee'] ?? 0,
                    'response_payload'  => json_encode($payload),
                ]
            );

            Log::info('Squad webhook: Payment marked as completed', [
                'donation_id'     => $donation->id,
                'transaction_ref' => $transactionRef,
            ]);
        } elseif (in_array($normalizedEvent, ['charge.failed', 'charge_failed'])) {
            if ($donation->status !== 'failed') {
                $donation->update(['status' => 'failed']);

                PaymentTransaction::updateOrCreate(
                    [
                        'payment_gateway'   => 'squad',
                        'payment_reference' => $transactionRef,
                    ],
                    [
                        'donation_id'       => $donation->id,
                        'donor_id'          => $donation->donor_id,
                        'project_id'        => $donation->project_id,
                        'category'          => $donation->project_id ? 'project' : 'general',
                        'event_type'        => 'charge.failed',
                        'gateway_reference' => $data['id'] ?? $data['transaction_ref'] ?? null,
                        'amount'            => $data['amount'] ?? $donation->amount,
                        'currency'          => 'NGN',
                        'status'            => 'failed',
                        'gateway_status'    => $data['status'] ?? 'failed',
                        'channel'           => $data['channel'] ?? $data['payment_type'] ?? null,
                        'fee'               => $data['fee'] ?? 0,
                        'response_payload'  => json_encode($payload),
                    ]
                );

                Log::info('Squad webhook: Payment marked as failed', [
                    'donation_id'     => $donation->id,
                    'transaction_ref' => $transactionRef,
                ]);
            }
        }

        return response()->json(['message' => 'Webhook processed'], 200);
    }

    private function failedView(?string $ref)
    {
        return view('donation-thank-you', [
            'success'   => false,
            'donorName' => 'Guest',
            'amount'    => 0,
            'email'     => '',
            'tierName'  => null,
            'ref'       => $ref,
            'emailSent' => false,
        ]);
    }

    /**
     * Send a thank-you email to a guest donor (not in our donors table)
     * by picking the email template with the lowest min_amount threshold met.
     */
    private function sendGuestThankYouEmail(string $email, string $name, float $amount, string $ref): bool
    {
        try {
            // Find the best matching tier template
            $template = \App\Models\EmailTemplate::join('donor_tiers', 'email_templates.donor_tier_id', '=', 'donor_tiers.id')
                ->where('email_templates.is_active', true)
                ->where('donor_tiers.min_amount', '<=', $amount)
                ->orderBy('donor_tiers.sort_order', 'desc')
                ->select('email_templates.*')
                ->first();

            // Fall back to any active template without a tier restriction
            if (!$template) {
                $template = \App\Models\EmailTemplate::where('is_active', true)
                    ->whereNull('donor_tier_id')
                    ->first();
            }

            if (!$template) {
                return false;
            }

            $variables = [
                'donor_name'       => $name,
                'donor_email'      => $email,
                'amount'           => '₦' . number_format($amount, 2),
                'reference'        => $ref,
                'payment_reference'=> $ref,
                'donation_date'    => now()->format('d M Y'),
                'project_name'     => 'GIVE ABU',
                'organization_name'=> 'GIVE ABU',
                'tier_name'        => '',
                'total_amount'     => '₦' . number_format($amount, 2),
                'donation_type'    => 'General Donation',
            ];

            $body    = $this->replaceVars($template->body_html ?? '', $variables);
            $subject = $this->replaceVars($template->subject ?? 'Thank you for your donation', $variables);

            Mail::html($body, function ($msg) use ($email, $name, $subject) {
                $msg->to($email, $name)
                    ->subject($subject)
                    ->from(
                        config('mail.from.address', 'noreply@abu-endowment.edu.ng'),
                        config('mail.from.name', 'GIVE ABU')
                    );
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Squad: guest thank-you email failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function replaceVars(string $text, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
            $text = str_replace('{{ ' . $key . ' }}', $value, $text);
            $text = str_replace('[' . $key . ']', $value, $text);
        }
        return $text;
    }
}
