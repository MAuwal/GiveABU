<?php

namespace App\Jobs;

use App\Models\Donation;
use App\Services\PaymentNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class DeliverPaymentNotification implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public int $uniqueFor = 600;

    public function __construct(public int $outboxId)
    {
        $this->onQueue('notifications');
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return (string) $this->outboxId;
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $row = DB::table('payment_notification_outbox')->find($this->outboxId);
        if (! $row || $row->status === 'delivered') {
            return;
        }
        if ($row->status === 'failed') {
            throw new \RuntimeException('Notification requires delivery reconciliation');
        }
        $donation = Donation::find($row->donation_id);
        if (! $donation || $donation->status !== 'completed') {
            return;
        }
        try {
            app(PaymentNotificationService::class)->send($donation, $row->gateway);
            // A claimed delivery without a positive result is uncertain; never resend it.
            $events = $donation->transactions()->where('payment_gateway', $row->gateway)->pluck('event_type');
            if ($events->contains('sms.failed') || $events->contains('notification.failed')
                || ($events->contains('sms.claimed') && ! $events->contains('sms.accepted'))
                || ($events->contains('notification.claimed') && ! $events->contains('notification.sent'))) {
                throw new \RuntimeException('Notification requires delivery reconciliation');
            }
            DB::table('payment_notification_outbox')->where('id', $row->id)->update(['status' => 'delivered', 'updated_at' => now()]);
        } catch (\Throwable $e) {
            DB::table('payment_notification_outbox')->where('id', $row->id)->update(['status' => 'failed', 'updated_at' => now()]);
            throw new \RuntimeException('Notification delivery failed; inspect claims before retrying');
        }
    }
}
