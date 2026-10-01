<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PaymentIntegrityPreflight extends Command
{
    protected $signature = 'payments:preflight';

    protected $description = 'Read-only donation reference and state checks before payment migrations';

    public function handle(): int
    {
        $duplicates = DB::table('donations')->select('payment_reference')->whereNotNull('payment_reference')
            ->groupBy('payment_reference')->havingRaw('COUNT(*) > 1')->get()->count();
        $unexpected = DB::table('donations')->whereNotIn('status', ['pending', 'success', 'completed', 'failed'])->count();
        $this->info("Duplicate reference groups: {$duplicates}; unexpected donation states: {$unexpected}.");
        $this->info('Reconcile duplicates against provider records; never delete or select a financial winner automatically.');

        return $duplicates || $unexpected ? self::FAILURE : self::SUCCESS;
    }
}
