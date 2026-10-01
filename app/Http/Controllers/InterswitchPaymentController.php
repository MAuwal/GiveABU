<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Donor;
use App\Models\PaymentTransaction;
use App\Services\TierNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            'callback_url' => 'nullable|url',
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
        $customerName = trim($request->input('customer_name', '')) ?: 'Valued Donor';
        $callbackUrl = $request->input('callback_url', url('/api/interswitch/redirect'));

        $donor = Donor::firstOrCreate(
            ['email' => $email],
            [
                'name' => $customerName,
                'surname' => '',
                'donor_type' => 'addressable_alumni',
            ]
        );

        $donation = app(\App\Services\PaymentReferenceService::class)->create([
            'donor_id' => $donor->id,
            'project_id' => null,
            'amount' => $amountNaira,
            'type' => 'endowment',
            'frequency' => 'onetime',
            'endowment' => 'yes',
            'status' => 'pending',
        ], 'interswitch');

        $this->upsertTransaction($donation->payment_reference, [
            'donation_id' => $donation->id,
            'donor_id' => $donor->id,
            'project_id' => null,
            'category' => 'general',
            'event_type' => 'payment.initialized',
            'gateway_reference' => null,
            'amount' => $amountNaira,
            'currency' => 'NGN',
            'status' => 'pending',
            'gateway_status' => 'initialized',
            'channel' => null,
            'fee' => 0,
        ]);

        return response()->json([
            'checkout_url' => $this->checkoutUrl,
            'payload' => [
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
            ],
        ]);

        // return response()->json([
        //     'success' => true,
        //     'checkout_url' => url('/api/interswitch/process-checkout/' . rawurlencode($donation->payment_reference)),
        //     'payload' => [
        //         'merchant_code' => $this->merchantCode,
        //         'pay_item_id' => $this->payItemId,
        //         'txn_ref' => $donation->payment_reference,
        //         'amount' => $amountKobo,
        //         'currency' => $this->currencyCode,
        //         'site_redirect_url' => $callbackUrl,
        //         'cust_name' => $customerName,
        //         'cust_email' => $email,
        //         'cust_id' => $donor->id,
        //         'pay_item_name' => 'ABU Giving Donation',
        //         'mode' => 'TEST',
        //     ],
        // ]);
    }

    /**
     * Serve an HTTPS page that POSTs the pending transaction to Interswitch.
     * Native Capacitor browsers cannot open the data: URI previously used for
     * this form submission.
     */
    public function processCheckout(string $txn_ref)
    {
        $donation = Donation::with('donor')
            ->where('payment_reference', $txn_ref)
            ->where('status', 'pending')
            ->firstOrFail();

        $donor = $donation->donor;
        $customerName = trim((string) ($donor?->name ?? '')) ?: 'Valued Donor';

        return response()
            ->view('interswitch-bouncer', [
                'action_url' => $this->checkoutUrl,
                'form_fields' => [
                    'merchant_code' => $this->merchantCode,
                    'pay_item_id' => $this->payItemId,
                    'txn_ref' => $donation->payment_reference,
                    'amount' => (int) round($donation->amount * 100),
                    'currency' => $this->currencyCode,
                    'site_redirect_url' => url('/api/interswitch/redirect'),
                    'cust_name' => $customerName,
                    'cust_email' => (string) ($donor?->email ?? ''),
                    'cust_id' => $donation->donor_id,
                    'pay_item_name' => 'ABU Giving Donation',
                    'mode' => 'TEST',
                ],
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * API Verification for Mobile App
     * GET /api/interswitch/verify/{reference}
     */
    public function verifyApi($reference)
    {
        if (! is_string($reference) || $reference === '') {
            return response()->json(['success' => false, 'message' => 'Missing reference'], 400);
        }
        $donation = Donation::where('payment_reference', $reference)->first();
        if (! $donation) {
            return response()->json(['success' => false, 'message' => 'Donation not found'], 404);
        }
        if ($donation->status === 'completed') {
            return response()->json(['success' => true, 'message' => 'Payment already verified', 'data' => ['status' => 'completed', 'amount' => $donation->amount, 'reference' => $reference]]);
        }
        try {
            $verification = $this->requeryTransaction($reference, \App\Services\PaymentAmount::kobo($donation->amount));
        } catch (\Throwable $e) {
            $this->recordVerification($donation, 'verification.unavailable', 'unavailable');
            Log::warning('Interswitch verification unavailable', ['donation_id' => $donation->id, 'exception' => get_class($e)]);

            return response()->json(['success' => false, 'message' => 'Verification temporarily unavailable. Please retry.', 'data' => ['status' => $donation->status]], 503);
        }
        $data = $verification['data'] ?? $verification;
        if (! is_array($data)) {
            $data = [];
        }
        $code = trim((string) ($data['ResponseCode'] ?? $data['responseCode'] ?? ''));
        $received = \App\Services\PaymentAmount::minor($data['Amount'] ?? $data['amount'] ?? null);
        $result = \Illuminate\Support\Facades\DB::transaction(function () use ($donation, $data, $code, $received, $reference) {
            if ($donation->project_id) {
                \App\Models\Project::whereKey($donation->project_id)->lockForUpdate()->firstOrFail();
            }
            $locked = Donation::whereKey($donation->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'completed') {
                return ['status' => 'completed', 'changed' => false];
            }
            $returnedReference = $data['MerchantReference'] ?? $data['merchantReference'] ?? $reference;
            if ($returnedReference !== $reference) {
                $this->recordVerification($locked, 'verification.rejected', 'reference_mismatch');

                return ['status' => $locked->status, 'reason' => 'reference_mismatch'];
            }
            if (in_array($code, ['00', '0', '000', '10', '11'], true)) {
                $expected = \App\Services\PaymentAmount::kobo($locked->amount);
                if ($received === null || $received !== $expected) {
                    $reason = $received === null ? 'invalid_amount' : 'amount_mismatch';
                    $this->recordVerification($locked, 'verification.rejected', $reason, ['expected_minor' => $expected, 'received_minor' => $received]);
                    Log::warning('Interswitch verified amount rejected', ['donation_id' => $locked->id, 'expected_minor' => $expected, 'received_minor' => $received]);

                    return ['status' => $locked->status, 'reason' => $reason];
                }
                $locked->update(['status' => 'completed', 'verified_at' => now(), 'paid_at' => now()]);
                $this->recordVerification($locked, 'charge.success', $code);
                if ($locked->project_id) {
                    app(\App\Services\ProjectFundingService::class)->rebuild($locked->project_id);
                }

                return ['status' => 'completed', 'changed' => true];
            }
            // Only explicitly confirmed declines/cancellation are terminal. Unknown,
            // processing and operational errors remain recoverable.
            if (in_array($code, ['05', '14', '17', '51', '54', '55', '57', '62', '65', 'Z6'], true)) {
                $locked->update(['status' => 'failed']);
                $this->recordVerification($locked, 'charge.failed', $code);

                return ['status' => 'failed'];
            }
            $this->recordVerification($locked, 'verification.pending', $code ?: 'unknown');

            return ['status' => $locked->status, 'reason' => 'pending'];
        }, 5);
        if ($result['changed'] ?? false) {
            app(\App\Services\PaymentSmsService::class)->send($donation, 'interswitch');
            $this->sendThankYouEmail($donation->fresh());
            try {
                app(TierNotificationService::class)->handleDonationTierCheck($donation->fresh());
            } catch (\Throwable $e) {
                Log::warning('Interswitch tier notification unavailable', ['donation_id' => $donation->id]);
            }
        }
        $success = $result['status'] === 'completed';

        return response()->json(['success' => $success, 'message' => $success ? 'Payment verified successfully' : 'Payment not completed',
            'data' => ['status' => $result['status'], 'amount' => $donation->amount, 'reference' => $reference, 'reason' => $result['reason'] ?? null]],
            in_array($result['reason'] ?? '', ['amount_mismatch', 'invalid_amount', 'reference_mismatch'], true) ? 409 : 200);
    }

    public function handleRedirect(Request $request)
    {
        $reference = $request->input('txnref') ?? $request->input('txn_ref') ?? $request->input('txnRef')
            ?? $request->input('merchantReference') ?? $request->input('merchant_reference')
            ?? $request->input('reference') ?? $request->input('transactionreference');
        $response = $this->verifyApi($reference);
        $result = $response->getData(true);
        $status = $result['data']['status'] ?? 'pending';

        return redirect($this->buildRedirectUrl($status === 'completed' ? 'success' : $status, is_string($reference) ? $reference : null, (float) ($result['data']['amount'] ?? 0)));
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('X-Interswitch-Signature');
        if (! $signature || empty($this->secretKey) || ! hash_equals(hash_hmac('sha512', $request->getContent(), $this->secretKey), $signature)) {
            return response('', 400);
        }
        $data = $request->json('data', []);
        $reference = $data['merchantReference'] ?? $data['MerchantReference'] ?? $data['txnref'] ?? $data['txn_ref'] ?? null;
        if (! is_string($reference) || $reference === '') {
            return response('', 400);
        }
        // Signed notifications still trigger independent provider verification.
        $response = $this->verifyApi($reference);

        return response('', $response->getStatusCode() === 503 ? 503 : 200);
    }

    private function recordVerification(Donation $donation, string $event, string $gatewayStatus, array $metadata = []): void
    {
        $eventKey = hash('sha256', json_encode(['interswitch', $donation->payment_reference, $event, $gatewayStatus, $metadata]));
        PaymentTransaction::firstOrCreate([
            'event_key' => $eventKey,
        ], [
            'payment_gateway' => 'interswitch', 'payment_reference' => $donation->payment_reference,
            'event_type' => $event, 'gateway_status' => $gatewayStatus,
            'donation_id' => $donation->id, 'donor_id' => $donation->donor_id, 'project_id' => $donation->project_id,
            'category' => $donation->project_id ? 'project' : 'general', 'amount' => $donation->amount,
            'currency' => 'NGN', 'status' => $donation->status, 'metadata' => $metadata,
        ]);
    }

    private function requeryTransaction(string $txnRef, $amountMinor)
    {
        $params = [
            'merchantcode' => $this->merchantCode,
            'transactionreference' => $txnRef,
        ];

        if (! is_null($amountMinor) && $amountMinor !== '') {
            $params['amount'] = $amountMinor;
        }

        $response = Http::acceptJson()
            ->timeout(30)
            ->get($this->baseUrl.'/collections/api/v1/gettransaction.json', $params);

        if (! $response->successful()) {
            Log::error('Interswitch transaction requery failed', [
                'txn_ref' => $txnRef,
                'amount' => $amountMinor,
                'status' => $response->status(),
            ]);
            throw new \Exception('Unable to verify Interswitch transaction.');
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new \UnexpectedValueException('Invalid provider response');
        }

        return $data;
    }

    private function buildRedirectUrl(string $status, ?string $reference, float $amount): string
    {
        $url = url('/');
        $query = http_build_query([
            'payment_status' => $status,
            'reference' => $reference,
            'amount' => $amount,
        ]);

        return $url.'?'.$query;
    }

    private function upsertTransaction(string $paymentReference, array $attributes): PaymentTransaction
    {
        return PaymentTransaction::updateOrCreate(
            [
                'payment_gateway' => 'interswitch',
                'payment_reference' => $paymentReference,
            ],
            array_merge([
                'event_type' => 'payment.initialized',
                'status' => 'pending',
                'gateway_status' => 'initialized',
                'currency' => 'NGN',
            ], $attributes)
        );
    }

    private function sendThankYouEmail($donation)
    {
        try {
            $donor = $donation->donor;
            if (! $donor || ! $donor->email) {
                return;
            }

            $donorName = trim(($donor->surname ?? '').' '.($donor->name ?? '')) ?: 'Valued Donor';
            \Illuminate\Support\Facades\Mail::send('emails.thank-you', [
                'donorName' => $donorName,
                'amount' => number_format($donation->amount, 2),
                'reference' => $donation->payment_reference,
                'projectName' => $donation->project ? $donation->project->project_title : 'ABU Giving',
                'donationDate' => $donation->paid_at ?? now(),
                'donationType' => 'ABU Giving Fund',
                'logoUrl' => 'https://abu-endowment.cloud/abu_logo_white_for_email.png',
            ], function ($message) use ($donor) {
                $message->from(config('mail.from.address', 'noreply@abu-endowment.edu.ng'), config('mail.from.name', 'ABU Giving'))
                    ->to($donor->email)
                    ->subject('Thank You for Your Donation to ABU Giving');
            });
        } catch (\Exception $e) {
            Log::error('Interswitch thank you email failed', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
