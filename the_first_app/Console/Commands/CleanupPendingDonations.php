<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Donation;
use Illuminate\Support\Facades\Log;

class CleanupPendingDonations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'donations:cleanup-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired pending donations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired pending donations...');
        
        try {
            // Delete donations that have been pending for more than 24 hours
            $deleted = Donation::where('status', 'pending')
                ->where('created_at', '<', now()->subDay())
                ->delete();

            Log::info('Cleaned up expired pending donations', ['deleted_count' => $deleted]);

            if ($deleted > 0) {
                $this->info("Successfully cleaned up {$deleted} expired pending donations.");
            } else {
                $this->info('No expired pending donations found to clean up.');
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            Log::error('Error cleaning up pending donations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error('Error cleaning up pending donations: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}