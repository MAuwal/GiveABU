<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectFundingService
{
    public function rebuild(int $projectId): void
    {
        DB::transaction(function () use ($projectId) {
            $project = Project::withTrashed()->whereKey($projectId)->lockForUpdate()->first();
            if (! $project) {
                return;
            }
            // Locking current reads avoid a stale MySQL REPEATABLE READ snapshot.
            $amounts = Donation::where('project_id', $projectId)->where('status', 'completed')
                ->orderBy('id')->lockForUpdate()->pluck('amount');
            $total = 0;
            foreach ($amounts as $amount) {
                $total += PaymentAmount::kobo($amount);
            }
            $project->update(['raised' => PaymentAmount::naira($total)]);
        }, 5);
    }
}
