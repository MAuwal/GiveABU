<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\ProjectFundingService;
use Illuminate\Console\Command;

class ReconcileProjectPayments extends Command
{
    protected $signature = 'payments:reconcile-projects';

    protected $description = 'Rebuild project raised totals from completed donations';

    public function handle(ProjectFundingService $funding): int
    {
        Project::withTrashed()->select('id')->orderBy('id')->chunkById(100, function ($projects) use ($funding) {
            foreach ($projects as $project) {
                $funding->rebuild($project->id);
            }
        });
        $this->info('Project totals rebuilt from completed donations.');

        return self::SUCCESS;
    }
}
