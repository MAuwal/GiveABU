<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use App\Services\PaymentAmount;
use App\Services\SquadPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SquadPaymentController extends Controller
{
    public function __construct(private SquadPaymentService $payments) {}

    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:100', 'regex:/\A\d{1,13}(?:\.\d{1,2})?\z/'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/\A\+?[0-9 ()-]{7,30}\z/'],
            'email' => 'required|email|max:255',
            'customer_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'callback_url' => 'nullable|string|max:2048',
            'project_id' => 'nullable|integer|exists:projects,id',
            'donor_id' => 'nullable|integer|exists:donors,id',
            'project_title' => 'nullable|string|max:255',
        ]);
        $callback = $request->input('callback_url');
        if ($callback && ! $this->allowedCallback($callback)) {
            throw ValidationException::withMessages(['callback_url' => 'Callback URL is not approved.']);
        }
        $secret = (string) config('services.squad.secret_key');
        if ($secret === '') {
            return response()->json(['message' => 'Payment provider is unavailable. Please contact support.'], 503);
        }
        $amountKobo = PaymentAmount::kobo($request->input('amount'));
        $amount = PaymentAmount::naira($amountKobo);
        $name = trim($request->input('customer_name') ?: $request->input('name', ''));
        $donor = $request->filled('donor_id') ? Donor::findOrFail($request->input('donor_id')) : null;
        if ($donor && strcasecmp((string) $donor->email, $request->input('email')) !== 0) {
            throw ValidationException::withMessages(['donor_id' => 'Donor does not match the supplied email.']);
        }
        $donor ??= Donor::firstOrCreate(['email' => $request->input('email')], [
            'name' => $name ?: 'Anonymous', 'surname' => '', 'donor_type' => 'addressable_alumni',
        ]);
        $donation = DB::transaction(function () use ($request, $donor, $amount) {
            $donation = app(\App\Services\PaymentReferenceService::class)->create([
                'receipt_phone' => $request->input('phone'),
                'donor_id' => $donor->id, 'project_id' => $request->input('project_id'), 'amount' => $amount,
                'type' => $request->filled('project_id') ? 'project' : 'endowment', 'frequency' => 'onetime',
                'endowment' => $request->filled('project_id') ? 'no' : 'yes', 'status' => 'pending',
            ], 'squad');
            $this->payments->event($donation, 'payment.initialized', [], 'initialized');

            return $donation;
        });
        $callback ??= url('/donation/thank-you').'?transaction_ref='.$donation->payment_reference;
        try {
            $response = Http::withToken($secret)->acceptJson()->connectTimeout(5)->timeout(30)
                ->post(rtrim(config('services.squad.base_url'), '/').'/transaction/initiate', [
                    'amount' => $amountKobo, 'email' => $donor->email, 'currency' => 'NGN',
                    'initiate_type' => 'inline', 'transaction_ref' => $donation->payment_reference,
                    'callback_url' => $callback, 'customer_name' => $name ?: $donor->name,
                    'metadata' => ['donation_id' => $donation->id, 'donor_id' => $donor->id, 'project_id' => $donation->project_id],
                ]);
            $checkout = $response->json('data.checkout_url');
            if (! $response->successful() || $response->json('success') !== true || ! is_string($checkout)
                || ! filter_var($checkout, FILTER_VALIDATE_URL) || parse_url($checkout, PHP_URL_SCHEME) !== 'https') {
                $this->payments->event($donation, 'initialization.unavailable', [], 'provider_response');

                return response()->json(['message' => 'Unable to initiate payment. Please try again later.',
                    'transaction_ref' => $donation->payment_reference], 503);
            }
            $this->payments->event($donation, 'initialization.accepted');

            return response()->json(['checkout_url' => $checkout, 'transaction_ref' => $donation->payment_reference]);
        } catch (\Throwable $e) {
            $this->payments->event($donation, 'initialization.unavailable', [], 'connection');
            Log::warning('Squad initialization unavailable', ['donation_id' => $donation->id, 'exception' => get_class($e)]);

            return response()->json(['message' => 'Unable to initiate payment. Please try again later.',
                'transaction_ref' => $donation->payment_reference], 503);
        }
    }

    private function allowedCallback(string $callback): bool
    {
        // Query strings are allowed; URL bases are exact, never suffix/substring matches.
        if (preg_match('/[\x00-\x20\\\\]/', $callback) || str_contains($callback, '#')) {
            return false;
        }
        $base = explode('?', $callback, 2)[0];
        $allowed = array_merge([url('/donation/thank-you')], config('services.squad.callback_urls', []));

        return in_array($base, $allowed, true);
    }

    public function verifyApi(string $reference)
    {
        try {
            $result = $this->payments->verify($reference);
            $donation = $result['donation'];
            $code = match ($result['outcome']) {
                'not_found' => 404, 'wrong_gateway', 'rejected' => 422, 'unavailable' => 503, default => 200,
            };

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Payment verified successfully' : 'Payment confirmation: '.$result['outcome'],
                'data' => ['status' => $donation?->status ?? 'pending', 'verification_status' => $result['outcome'],
                    'amount' => $donation?->amount, 'reference' => $reference],
            ], $code);
        } catch (\Throwable $e) {
            Log::error('Squad verification could not be recorded', ['exception' => get_class($e)]);

            return response()->json(['success' => false, 'message' => 'Payment confirmation is temporarily unavailable.'], 503);
        }
    }

    public function confirm(Request $request)
    {
        $reference = $request->query('transaction_ref');
        $result = ['success' => false, 'donation' => null, 'outcome' => 'pending'];
        if (is_string($reference) && strlen($reference) <= 255) {
            try {
                $result = $this->payments->verify($reference);
            } catch (\Throwable $e) {
                Log::error('Squad callback verification unavailable', ['exception' => get_class($e)]);
                $result['outcome'] = 'unavailable';
            }
        }
        $donation = $result['donation'];
        $donor = $donation?->donor;

        return view('donation-thank-you', [
            'success' => $result['success'], 'paymentState' => $result['outcome'],
            'donorName' => $result['success'] ? (trim(($donor?->surname ?? '').' '.($donor?->name ?? '')) ?: 'Valued Donor') : 'Guest',
            'amount' => $result['success'] ? $donation->amount : 0, 'email' => $result['success'] ? ($donor?->email ?? '') : '',
            'tierName' => $result['success'] ? $donor?->tier?->name : null,
            'ref' => is_string($reference) ? $reference : null,
            'emailSent' => $result['success'] && $donation->transactions()->where('event_type', 'notification.sent')->exists(),
        ]);
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('x-squad-encrypted-body');
        $secret = (string) config('services.squad.secret_key');
        if ($secret === '' || ! is_string($signature) || ! preg_match('/\A[0-9A-Fa-f]{128}\z/', $signature)
            || ! hash_equals(strtoupper(hash_hmac('sha512', $request->getContent(), $secret)), strtoupper($signature))) {
            return response()->json(['message' => 'Invalid webhook signature'], 401);
        }
        $reference = $request->input('Body.transaction_ref') ?? $request->input('data.transaction_ref')
            ?? $request->input('TransactionRef') ?? $request->input('transaction_ref');
        if (! is_string($reference) || $reference === '' || strlen($reference) > 255) {
            return response()->json(['message' => 'Missing transaction reference'], 400);
        }
        try {
            $result = $this->payments->verify($reference);

            // Retry outages; acknowledge safe rejections and unknown references for manual reconciliation.
            return response()->json(['message' => 'Payment confirmation: '.$result['outcome']], $result['outcome'] === 'unavailable' ? 503 : 200);
        } catch (\Throwable $e) {
            Log::error('Squad webhook processing unavailable', ['exception' => get_class($e)]);

            return response()->json(['message' => 'Please retry payment confirmation'], 503);
        }
    }
}
