<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\PaymentTransaction;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SquadPaymentService
{
    public function verify(string $reference): array
    {
        $donation = Donation::where('payment_reference', $reference)->first();
        if (! $donation || $donation->payment_reference !== $reference) {
            return $this->result(null, 'not_found');
        }
        if (! $this->belongsToSquad($donation)) {
            return $this->result($donation, 'wrong_gateway');
        }
        if ($donation->status === 'completed') {
            return $this->result($donation, 'completed');
        }

        $secret = (string) config('services.squad.secret_key');
        $data = [];
        $outcome = 'unavailable';
        if ($secret !== '') {
            try {
                $response = Http::withToken($secret)->acceptJson()->connectTimeout(5)->timeout(20)
                    ->get(rtrim(config('services.squad.base_url'), '/').'/transaction/verify/'.rawurlencode($reference));
                $raw = $response->json();
                if ($response->successful() && is_array($raw) && ($raw['success'] ?? false) === true
                    && is_array($raw['data'] ?? null)) {
                    $data = $raw['data'];
                    $outcome = 'verified';
                }
            } catch (\Throwable $e) {
                // Exceptions can contain request headers/customer data. Record only a safe class.
                Log::warning('Squad verification unavailable', ['donation_id' => $donation->id, 'exception' => get_class($e)]);
            }
        }

        return DB::transaction(function () use ($donation, $data, $outcome) {
            // Shared project-first lock ordering with reconciliation.
            $project = $donation->project_id
                ? Project::withTrashed()->whereKey($donation->project_id)->lockForUpdate()->first()
                : null;
            $locked = Donation::whereKey($donation->id)->lockForUpdate()->firstOrFail();
            if (! $this->belongsToSquad($locked)) {
                return $this->result($locked, 'wrong_gateway');
            }
            if ($locked->status === 'completed') {
                return $this->result($locked, 'completed');
            }
            if ($outcome === 'unavailable') {
                $this->event($locked, 'verification.unavailable', [], 'unavailable');

                return $this->result($locked, 'unavailable');
            }

            $status = is_string($data['transaction_status'] ?? null) ? strtolower(trim($data['transaction_status'])) : '';
            $currency = $data['transaction_currency_id'] ?? $data['currency'] ?? null;
            $amount = PaymentAmount::minor($data['transaction_amount'] ?? null);
            $reason = match (true) {
                ($data['transaction_ref'] ?? null) !== $locked->payment_reference => 'reference_mismatch',
                $currency !== 'NGN' => 'currency_mismatch',
                $amount === null || $amount !== PaymentAmount::kobo($locked->amount) => 'amount_mismatch',
                default => null,
            };
            if ($reason) {
                $this->event($locked, 'verification.rejected', $data, $reason);
                Log::warning('Squad verification rejected', ['donation_id' => $locked->id, 'reason' => $reason]);

                return $this->result($locked, 'rejected');
            }
            if ($status === 'success') {
                $locked->update(['status' => 'completed', 'paid_at' => now(), 'verified_at' => now()]);
                $this->event($locked, 'charge.success', $data, $status);
                if ($project) {
                    // Serialize project totals and keep model closing behavior. Rebuild from current reads.
                    app(ProjectFundingService::class)->rebuild($project->id);
                }
                DB::afterCommit(function () use ($locked) {
                    try {
                        app(PaymentNotificationService::class)->send($locked->fresh());
                    } catch (\Throwable $e) {
                        try {
                            $this->event($locked, 'notification.failed');
                        } catch (\Throwable $auditError) {
                            // A post-commit audit outage must not misreport a recorded payment.
                        }
                        Log::error('Payment notification failed after commit', ['donation_id' => $locked->id, 'exception' => get_class($e)]);
                    }
                });

                return $this->result($locked, 'completed');
            }
            if (in_array($status, ['failed', 'abandoned', 'cancelled', 'canceled', 'declined'], true)) {
                $locked->update(['status' => 'failed', 'verified_at' => now()]);
                $this->event($locked, 'charge.failed', $data, $status);

                return $this->result($locked, 'failed');
            }
            if (in_array($status, ['pending', 'processing'], true)) {
                $locked->update(['status' => 'pending', 'verified_at' => now()]);
            }
            $this->event($locked, 'verification.pending', $data, $status ?: 'unknown');

            return $this->result($locked, 'pending');
        }, 5);
    }

    private function belongsToSquad(Donation $donation): bool
    {
        $records = $donation->transactions()->where('payment_reference', $donation->payment_reference)->get();

        return $records->isNotEmpty() && $records->every(fn ($record) => $record->payment_gateway === 'squad');
    }

    public function event(Donation $donation, string $event, array $data = [], string $gatewayStatus = ''): PaymentTransaction
    {
        // Store only reconciliation fields: no emails, PANs, tokens, metadata or raw provider errors.
        $evidence = array_intersect_key($data, array_flip([
            'transaction_ref', 'transaction_amount', 'transaction_currency_id', 'currency', 'transaction_status',
            'gateway_transaction_ref', 'transaction_type',
        ]));
        ksort($evidence);
        $identity = in_array($event, ['charge.success', 'payment.initialized'], true) ? '' : json_encode([$gatewayStatus, $evidence]);
        $key = hash('sha256', json_encode(['squad', $donation->payment_reference, $event, $identity]));

        return PaymentTransaction::firstOrCreate(['event_key' => $key], [
            'donation_id' => $donation->id,
            'donor_id' => $donation->donor_id,
            'project_id' => $donation->project_id,
            'payment_gateway' => 'squad',
            'payment_reference' => $donation->payment_reference,
            'category' => $donation->project_id ? 'project' : 'general',
            'event_type' => $event,
            'gateway_reference' => is_string($data['gateway_transaction_ref'] ?? null) ? substr($data['gateway_transaction_ref'], 0, 255) : null,
            'channel' => is_string($data['transaction_type'] ?? null) ? substr($data['transaction_type'], 0, 255) : null,
            'amount' => $donation->amount,
            'currency' => 'NGN',
            'status' => $event === 'charge.success' ? 'completed' : ($event === 'charge.failed' ? 'failed' : 'pending'),
            'gateway_status' => $gatewayStatus,
            'message' => $gatewayStatus,
            'response_payload' => json_encode($evidence),
        ]);
    }

    private function result(?Donation $donation, string $outcome): array
    {
        return ['success' => $outcome === 'completed', 'outcome' => $outcome, 'donation' => $donation];
    }
}
