<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class TruncateDonors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:truncate-donors {--force : Do not ask for confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate donors, donor_sessions and device_sessions tables (destructive).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('This will permanently delete all rows in donors, donor_sessions and device_sessions. Do you wish to continue?')) {
                $this->info('Aborted.');
                return 1;
            }
        }

        $tables = [
            'donor_sessions',
            'device_sessions',
            'donors',
        ];

        $this->info('Disabling foreign key checks (if supported)...');

        // Try disabling for MySQL and SQLite; ignore failures for other drivers
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } catch (Exception $e) {
            // ignore
        }

        try {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } catch (Exception $e) {
            // ignore
        }

        foreach ($tables as $table) {
            try {
                $this->line("Truncating table: {$table}...");
                DB::table($table)->truncate();
                $this->info("Truncated: {$table}");
            } catch (Exception $e) {
                $this->error("Failed to truncate {$table}: " . $e->getMessage());
                // fallback to DELETE in case TRUNCATE is restricted
                try {
                    $this->line("Attempting DELETE fallback for {$table}...");
                    DB::table($table)->delete();
                    $this->info("Deleted rows from: {$table}");
                } catch (Exception $ex) {
                    $this->error("Fallback delete failed for {$table}: " . $ex->getMessage());
                    return 1;
                }
            }
        }

        $this->info('Re-enabling foreign key checks (if supported)...');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (Exception $e) {
            // ignore
        }

        try {
            DB::statement('PRAGMA foreign_keys = ON;');
        } catch (Exception $e) {
            // ignore
        }

        $this->info('Done.');

        return 0;
    }
}
