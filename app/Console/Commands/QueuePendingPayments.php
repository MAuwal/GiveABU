<?php

namespace App\Console\Commands;

use App\Jobs\VerifyPendingPayment;
use App\Models\Donation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class QueuePendingPayments extends Command
{
    protected $signature = 'payments:queue-pending {--limit=500 : Maximum donations scanned in this run}';

    protected $description = 'Queue bounded recovery of pending Squad and Interswitch payments';

    public function handle(): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        if (! $limit || $limit < 1 || $limit > 10000) {
            $this->error('Limit must be between 1 and 10000.');

            return self::FAILURE;
        }
        $cursor = (int) Cache::get('payment-recovery-cursor', 0);
        $query = Donation::where('status', 'pending')->whereNotNull('payment_reference')
            ->where('created_at', '<', now()->subSeconds(30))
            ->whereHas('transactions', fn ($query) => $query->whereIn('payment_gateway', ['squad', 'interswitch']));
        $ids = (clone $query)->where('id', '>', $cursor)->orderBy('id')->limit($limit)->pluck('id');
        if ($ids->isEmpty() && $cursor > 0) {
            $ids = $query->orderBy('id')->limit($limit)->pluck('id');
        }
        foreach ($ids as $id) {
            VerifyPendingPayment::dispatch((int) $id);
        }
        Cache::put('payment-recovery-cursor', (int) ($ids->last() ?? 0), 86400);
        $this->info('Queued '.$ids->count().' pending payment candidates.');

        return self::SUCCESS;
    }
}
