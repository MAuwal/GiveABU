<?php

namespace App\Services;

use App\Jobs\DeliverPaymentNotification;
use App\Models\Donation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentNotificationOutbox
{
    public function enqueue(Donation $donation, string $gateway): void
    {
        DB::transaction(function () use ($donation, $gateway) {
            $current = Donation::whereKey($donation->id)->lockForUpdate()->first();
            if (! $current || $current->status !== 'completed') {
                return;
            }
            DB::table('payment_notification_outbox')->insertOrIgnore([
                'donation_id' => $current->id, 'gateway' => $gateway, 'status' => 'pending',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $id = DB::table('payment_notification_outbox')->where('donation_id', $current->id)->where('gateway', $gateway)->value('id');
            DB::afterCommit(fn () => $this->publish($id));
        });
    }

    public function publish(int $id): void
    {
        $row = DB::table('payment_notification_outbox')->find($id);
        if (! $row || $row->status !== 'pending') {
            return;
        }
        try {
            if (app()->environment('production') && config('queue.connections.'.config('queue.default').'.driver') === 'sync') {
                throw new \RuntimeException('Production receipt queue must be asynchronous');
            }
            DeliverPaymentNotification::dispatch($id);
            DB::table('payment_notification_outbox')->where('id', $id)->update(['published_at' => now(), 'updated_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('Payment notification publish unavailable', ['outbox_id' => $id, 'exception' => get_class($e)]);
        }
    }
}
