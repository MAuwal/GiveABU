<?php

namespace App\Console\Commands;

use App\Models\Donation;
use Illuminate\Console\Command;

class CleanupPendingDonations extends Command
{
    protected $signature = 'donations:cleanup-pending';

    protected $description = 'Report unresolved payments without deleting financial records';

    public function handle(): int
    {
        $count = Donation::where('status', 'pending')->where('created_at', '<', now()->subDay())->count();
        $this->info("{$count} pending donations require reconciliation. No financial records deleted.");

        return Command::SUCCESS;
    }
}
