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

class InterswitchPaymentController extends Controller
{
    private string $merchantCode;
    private string $payItemId;
    private string $secretKey;
    private string $baseUrl;
    private string $checkoutUrl;
    private string $currencyCode;

    public function __construct()
    {
        $this->merchantCode = config('services.interswitch.merchant_code', '');
        $this->payItemId = config('services.interswitch.pay_item_id', '');
        $this->secretKey = config('services.interswitch.secret_key', '');
        $this->baseUrl = rtrim(config('services.interswitch.base_url', 'https://sandbox.interswitchng.com'), '/');
        $this->checkoutUrl = config('services.interswitch.checkout_url', 'https://newwebpay-sandbox.interswitchng.com/collections/w/pay');
        $this->currencyCode = config('services.interswitch.currency_code', '566');
    }

    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'email' => 'required|email',
            'customer_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'callback_url' => 'nullable|string',
            'project_id' => 'nullable|integer',
            'donor_id' => 'nullable|integer',
            'project_title' => 'nullable|string',
        ]);

        if (empty($this->merchantCode) || empty($this->payItemId)) {
            Log::error('Interswitch configuration missing');
            return response()->json([
                'message' => 'Interswitch payment provider is not configured. Please contact support.',
            ], 500);
        }

        $amountNaira = (float) $request->input('amount');
        $amountKobo = (int) round($amountNaira * 100);
        $email = $request->input('email');
        $customerName = trim($request->input('customer_name', $request->input('name', ''))) ?: 'Valued Donor';
        $callbackUrl = $request->input('callback_url', url('/api/interswitch/redirect'));
        $projectId = $request->input('project_id');
        $donorId = $request->input('donor_id');

        $donor = Donor::firstOrCreate(
            ['email' => $email],
            [
                'name' => $customerName,
                'surname' => '',
                'donor_type' => 'addressable_alumni',
            ]
        );

        $donation = Donation::create([
            'donor_id' => $donorId ?: $donor->id,
            'project_id' => $projectId,
            'amount' => $amountNaira,
            'type' => $projectId ? 'project' : 'endowment',
            'frequency' => 'onetime',
            'endowment' => $projectId ? 'no' : 'yes',
            'status' => 'pending',
            'payment_reference' => 'ABU_INTSWITCH_' . time() . '_' . uniqid(),
        ]);

        PaymentTransaction::create([
            'donation_id' => $donation->id,
            'donor_id' => $donorId ?: $donor->id,
            'project_id' => $projectId,
            'payment_gateway' => 'interswitch',
            'category' => $projectId ? 'project' : 'general',
            'event_type' => 'payment.initialized',
            'payment_reference' => $donation->payment_reference,
            'gateway_reference' => null,
            'amount' => $amountNaira,
            'currency' => 'NGN',
            'status' => 'pending',
            'gateway_status' => 'initialized',
            'channel' => null,
            'fee' => 0,
        ]);

        $payload = [
            'merchant_code' => $this->merchantCode,
            'pay_item_id' => $this->payItemId,
            'txn_ref' => $donation->payment_reference,
            'amount' => $amountKobo,
            'currency' => $this->currencyCode,
            'site_redirect_url' => $callbackUrl,
            'cust_name' => $customerName,
            'cust_email' => $email,
            'cust_id' => $donor->id,
            'pay_item_name' => 'ABU Giving Donation',
            'mode' => 'TEST',
        ];

        \Illuminate\Support\Facades\Cache::put("isw_payload_{$donation->payment_reference}", $payload, now()->addHours(1));

        return response()->json([
            'checkout_url' => url("/api/interswitch/process-checkout/{$donation->payment_reference}"),
            'payload' => $payload,
        ]);
    }

    public function processCheckout($txn_ref)
    {
        $payload = \Illuminate\Support\Facades\Cache::get("isw_payload_{$txn_ref}");
        
        if (!$payload) {
            echo "Checkout session expired or invalid transaction reference.";
            exit;
        }

        // Force raw HTML output to bypass any API middleware forcibly slapping application/json onto the response
        header('Content-Type: text/html; charset=utf-8');
        echo view('interswitch-bouncer', [
            'action_url' => $this->checkoutUrl,
            'form_fields' => $payload
        ])->render();
        exit;
    }

    public function handleRedirect(Request $request)
    {
        $txnRef = $request->input('txnref') ?? $request->input('txn_ref') ?? $request->input('txnRef');
        $amountMinor = $request->input('amount');
        $responseCode = $request->input('resp') ?? $request->input('responseCode');

        if (!$txnRef) {
            Log::warning('Interswitch redirect missing txnref', ['request' => $request->all()]);
            return redirect($this->buildRedirectUrl('failed', null, 0));
        }

        try {
            $verification = $this->requeryTransaction($txnRef, $amountMinor);
            $data = $verification['data'] ?? $verification;
            $responseCode = $data['ResponseCode'] ?? $data['responseCode'] ?? $responseCode;
            $amountPaid = isset($data['Amount']) ? ((int) $data['Amount'] / 100) : ($amountMinor ? ((int) $amountMinor / 100) : 0);
            $paymentReference = $data['PaymentReference'] ?? $data['paymentReference'] ?? null;
            $merchantReference = $data['MerchantReference'] ?? $data['merchantReference'] ?? $txnRef;

            $donation = Donation::where('payment_reference', $merchantReference)->first();

            if (!$donation) {
                Log::warning('Interswitch redirect donation not found', ['txn_ref' => $txnRef]);
                return redirect($this->buildRedirectUrl('failed', $txnRef, $amountPaid));
            }

            $success = trim((string) $responseCode) === '00';

            if ($success) {
                $donation->update(['status' => 'completed', 'verified_at' => now(), 'paid_at' => now(), 'amount' => $amountPaid]);

                PaymentTransaction::create([
                    'donation_id' => $donation->id,
                    'donor_id' => $donation->donor_id,
                    'project_id' => $donation->project_id,
                    'payment_gateway' => 'interswitch',
                    'category' => $donation->project_id ? 'project' : 'general',
                    'event_type' => 'charge.success',
                    'payment_reference' => $merchantReference,
                    'gateway_reference' => $paymentReference ?? $txnRef,
                    'amount' => $amountPaid,
                    'currency' => 'NGN',
                    'status' => 'completed',
                    'gateway_status' => (string) $responseCode,
                    'channel' => $data['Channel'] ?? $data['channel'] ?? null,
                    'fee' => 0,
                    'response_payload' => json_encode($data),
                ]);

                return redirect($this->buildRedirectUrl('success', $merchantReference, $amountPaid));
            }

            $donation->update(['status' => 'failed']);

            PaymentTransaction::create([
                'donation_id' => $donation->id,
                'donor_id' => $donation->donor_id,
                'project_id' => $donation->project_id,
                'payment_gateway' => 'interswitch',
                'category' => $donation->project_id ? 'project' : 'general',
                'event_type' => 'charge.failed',
                'payment_reference' => $merchantReference,
                'gateway_reference' => $paymentReference ?? $txnRef,
                'amount' => $amountPaid,
                'currency' => 'NGN',
                'status' => 'failed',
                'gateway_status' => (string) $responseCode,
                'channel' => $data['Channel'] ?? $data['channel'] ?? null,
                'fee' => 0,
                'response_payload' => json_encode($data),
            ]);

            return redirect($this->buildRedirectUrl('failed', $merchantReference, $amountPaid));
        } catch (\Exception $e) {
            Log::error('Interswitch redirect error', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return redirect($this->buildRedirectUrl('failed', $txnRef, 0));
        }
    }

    /**
     * API Verification for Mobile App
     * GET /api/interswitch/verify/{reference}
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

            // We must pass the amount as Minor to requery for Interswitch.
            // If the amount was already paid or known, we use the donation amount.
            $amountMinor = (int) round($donation->amount * 100);
            
            try {
                $verification = $this->requeryTransaction($reference, $amountMinor);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Gateway check failed: ' . $e->getMessage()], 400);
            }

            $data = $verification['data'] ?? $verification;
            $responseCode = $data['ResponseCode'] ?? $data['responseCode'] ?? null;
            $amountPaid = isset($data['Amount']) ? ((int) $data['Amount'] / 100) : $donation->amount;
            $paymentReference = $data['PaymentReference'] ?? $data['paymentReference'] ?? null;
            $merchantReference = $data['MerchantReference'] ?? $data['merchantReference'] ?? $reference;

            $success = trim((string) $responseCode) === '00';

            if ($success) {
                $donation->update(['status' => 'completed', 'verified_at' => now(), 'paid_at' => now(), 'amount' => $amountPaid]);

                PaymentTransaction::create([
                    'donation_id' => $donation->id,
                    'donor_id' => $donation->donor_id,
                    'project_id' => $donation->project_id,
                    'payment_gateway' => 'interswitch',
                    'category' => $donation->project_id ? 'project' : 'general',
                    'event_type' => 'charge.success',
                    'payment_reference' => $merchantReference,
                    'gateway_reference' => $paymentReference ?? $reference,
                    'amount' => $amountPaid,
                    'currency' => 'NGN',
                    'status' => 'completed',
                    'gateway_status' => (string) $responseCode,
                    'channel' => $data['Channel'] ?? $data['channel'] ?? null,
                    'fee' => 0,
                    'response_payload' => json_encode($data),
                ]);

                if ($donation->project_id) {
                    $project = \App\Models\Project::find($donation->project_id);
                    if ($project) {
                        $raised = Donation::where('project_id', $donation->project_id)->where('status', 'completed')->sum('amount');
                        $project->update(['raised' => $raised]);
                    }
                }

                $this->sendThankYouEmail($donation);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'data' => [
                        'status' => 'completed',
                        'amount' => $amountPaid,
                        'reference' => $reference
                    ]
                ]);
            }

            // Not successful (but maybe abandoned or explicitly failed)
            if (!empty($responseCode) && !in_array(trim((string)$responseCode), ['Z0', '00'])) {
                $donation->update(['status' => 'failed']);
                // Note: We don't necessarily log a failed transaction here unless it was explicitly declined to avoid DB spam.
            }

            return response()->json(['success' => false, 'message' => 'Payment not successful', 'data' => ['status' => (string)$responseCode]]);

        } catch (\Exception $e) {
            Log::error('Interswitch API verify exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error during verification'], 500);
        }
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('X-Interswitch-Signature');
        $payload = $request->getContent();

        if (!$signature || empty($this->secretKey)) {
            Log::warning('Interswitch webhook signature missing or secret not configured', ['signature' => $signature ? 'present' : 'missing']);
            return response('', 400);
        }

        $computedSignature = hash_hmac('sha512', $payload, $this->secretKey);

        if (!hash_equals($computedSignature, $signature)) {
            Log::warning('Interswitch webhook signature mismatch', ['received' => $signature, 'computed' => $computedSignature]);
            return response('', 400);
        }

        $payloadArr = json_decode($payload, true) ?: [];
        $data = $payloadArr['data'] ?? $payloadArr;

        $merchantReference = $data['merchantReference'] ?? $data['MerchantReference'] ?? $data['txnref'] ?? $data['txn_ref'] ?? null;
        $responseCode = $data['responseCode'] ?? $data['ResponseCode'] ?? $data['resp'] ?? null;
        $confirmedAmount = isset($data['amount']) ? ((int) $data['amount'] / 100) : null;

        if (!$merchantReference) {
            Log::warning('Interswitch webhook missing merchantReference', ['payload' => $payloadArr]);
            return response('', 200);
        }

        $donation = Donation::where('payment_reference', $merchantReference)->first();
        if (!$donation) {
            Log::warning('Interswitch webhook donation not found', ['merchantReference' => $merchantReference, 'payload' => $payloadArr]);
            return response('', 200);
        }

        if ($donation->status === 'completed') {
            return response('', 200);
        }

        $isSuccess = trim((string) $responseCode) === '00';

        if ($isSuccess) {
            $donation->update([
                'status' => 'completed',
                'verified_at' => now(),
                'paid_at' => now(),
                'amount' => $confirmedAmount ?? $donation->amount,
            ]);

            PaymentTransaction::create([
                'donation_id' => $donation->id,
                'donor_id' => $donation->donor_id,
                'project_id' => $donation->project_id,
                'payment_gateway' => 'interswitch',
                'category' => $donation->project_id ? 'project' : 'general',
                'event_type' => 'charge.success',
                'payment_reference' => $merchantReference,
                'gateway_reference' => $data['paymentReference'] ?? $data['PaymentReference'] ?? null,
                'amount' => $confirmedAmount ?? $donation->amount,
                'currency' => 'NGN',
                'status' => 'completed',
                'gateway_status' => (string) $responseCode,
                'channel' => $data['channel'] ?? $data['Channel'] ?? null,
                'fee' => 0,
                'response_payload' => json_encode($payloadArr),
            ]);

            $this->sendThankYouEmail($donation);
            return response('', 200);
        }

        if ($donation->status !== 'failed') {
            $donation->update(['status' => 'failed']);
            PaymentTransaction::create([
                'donation_id' => $donation->id,
                'donor_id' => $donation->donor_id,
                'project_id' => $donation->project_id,
                'payment_gateway' => 'interswitch',
                'category' => $donation->project_id ? 'project' : 'general',
                'event_type' => 'charge.failed',
                'payment_reference' => $merchantReference,
                'gateway_reference' => $data['paymentReference'] ?? $data['PaymentReference'] ?? null,
                'amount' => $confirmedAmount ?? $donation->amount,
                'currency' => 'NGN',
                'status' => 'failed',
                'gateway_status' => (string) $responseCode,
                'channel' => $data['channel'] ?? $data['Channel'] ?? null,
                'fee' => 0,
                'response_payload' => json_encode($payloadArr),
            ]);
        }

        return response('', 200);
    }

    private function requeryTransaction(string $txnRef, $amountMinor)
    {
        $params = [
            'merchantcode' => $this->merchantCode,
            'transactionreference' => $txnRef,
            'amount' => $amountMinor,
        ];

        $response = Http::acceptJson()
            ->timeout(30)
            ->get($this->baseUrl . '/collections/api/v1/gettransaction.json', $params);

        if ($response->failed()) {
            Log::error('Interswitch transaction requery failed', [
                'txn_ref' => $txnRef,
                'amount' => $amountMinor,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Unable to verify Interswitch transaction.');
        }

        return $response->json();
    }

    private function buildRedirectUrl(string $status, ?string $reference, float $amount): string
    {
        $url = url('/');
        $query = http_build_query([
            'payment_status' => $status,
            'reference' => $reference,
            'amount' => $amount,
        ]);
        return $url . '?' . $query;
    }

    private function sendThankYouEmail($donation)
    {
        try {
            $donor = $donation->donor;
            if (!$donor || !$donor->email) {
                return;
            }

            // Route via tier service to send a thank-you message by user tier
            $donation->load('donor', 'project');
            (new TierNotificationService())->handleDonationTierCheck($donation);
            
            // Fallback for Guests or if tier notification is skipped is handled intrinsically
            // if TierNotificationService works exactly like Squad implementation expects.
            
        } catch (\Exception $e) {
            Log::error('Interswitch thank you email/tier check failed', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
