<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentSmsService
{
    public function send(Donation $donation, string $gateway): void
    {
        // Schedule external effects after the outermost financial commit.
        DB::afterCommit(function () use ($donation, $gateway) {
            try {
                $donation = $donation->fresh(['donor', 'project']);
                if (! $donation || $donation->status !== 'completed' || ! ($donation->receipt_phone ?: $donation->donor?->phone)) {
                    return;
                }
                $claim = $this->event($donation, $gateway, 'sms.claimed');
                if (! $claim->wasRecentlyCreated) {
                    return;
                }
                $result = app(SmsService::class)->sendDonationConfirmationSms(
                    $donation->receipt_phone ?: $donation->donor->phone,
                    trim($donation->donor->surname.' '.$donation->donor->name) ?: 'Donor',
                    $donation->amount,
                    $donation->project?->project_title ?? 'GIVE ABU Fund',
                    $donation->payment_reference
                );
                $this->event($donation, $gateway, $result['success'] ? 'sms.accepted' : 'sms.failed');
            } catch (\Throwable $e) {
                Log::warning('Payment SMS unavailable after commit', ['donation_id' => $donation->id ?? null, 'exception' => get_class($e)]);
            }
        });
    }

    private function event(Donation $donation, string $gateway, string $event): PaymentTransaction
    {
        return PaymentTransaction::firstOrCreate(['event_key' => hash('sha256', $gateway.'|'.$donation->payment_reference.'|'.$event)], [
            'payment_gateway' => $gateway, 'payment_reference' => $donation->payment_reference,
            'donation_id' => $donation->id, 'event_type' => $event, 'status' => 'completed',
            'amount' => $donation->amount, 'currency' => 'NGN',
        ]);
    }
}
