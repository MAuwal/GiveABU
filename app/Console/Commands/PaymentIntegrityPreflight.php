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

        $unbound = DB::table('donations')->where('status', 'pending')
            ->where('payment_reference', 'like', 'ABU_ZARIA_SQUAD_%')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('payment_transactions')
                    ->whereColumn('payment_transactions.payment_reference', 'donations.payment_reference')
                    ->where('payment_transactions.payment_gateway', 'squad');
            })->get(['id', 'payment_reference']);
        // LIKE treats underscores as wildcards: enforce the literal prefix too.
        $unbound = $unbound->filter(fn ($row) => str_starts_with($row->payment_reference, 'ABU_ZARIA_SQUAD_'));
        $this->warn('Pending Squad-style references without Squad binding: '.$unbound->count());
        foreach ($unbound as $row) {
            $this->line("Donation {$row->id}: {$row->payment_reference}");
        }
        if ($unbound->isNotEmpty()) {
            $this->warn('Reconcile against Squad records before deployment; these payments would return wrong_gateway. No bindings were created.');
        }

        return $duplicates || $unexpected || $unbound->isNotEmpty() ? self::FAILURE : self::SUCCESS;
    }
}
