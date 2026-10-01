<?php

namespace App\Console\Commands;

use App\Services\PaymentNotificationOutbox;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchPaymentNotifications extends Command
{
    protected $signature = 'payments:dispatch-notifications {--limit=500}';

    protected $description = 'Republish durable pending receipt notifications without replaying claimed deliveries';

    public function handle(): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        if (! $limit || $limit < 1 || $limit > 10000) {
            return self::FAILURE;
        }
        $ids = DB::table('payment_notification_outbox')->where('status', 'pending')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<', now()->subMinutes(10)))
            ->orderBy('id')->limit($limit)->pluck('id');
        foreach ($ids as $id) {
            app(PaymentNotificationOutbox::class)->publish((int) $id);
        }
        $this->info('Scanned '.$ids->count().' notification outbox records.');

        return self::SUCCESS;
    }
}
