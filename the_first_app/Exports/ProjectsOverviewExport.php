<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProjectsOverviewExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $projects;

    public function __construct($projects)
    {
        $this->projects = $projects;
    }

    public function collection()
    {
        return $this->projects;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Project Name',
            'Description',
            'Target Amount (₦)',
            'Raised Amount (₦)',
            'Progress (%)',
            'Donor Count',
        ];
    }

    public function map($project): array
    {
        $raised = $project->raised_amount ?? ($project->raised ?? 0);
        $target = $project->target ?? 0;
        $percentage = ($target > 0) ? ($raised / $target) * 100 : 0;

        return [
            $project->id ?? 'N/A',
            $project->project_title ?? 'Endowment Project',
            $project->project_description ?? '',
            $target > 0 ? number_format($target, 2, '.', '') : 'N/A',
            number_format($raised, 2, '.', ''),
            number_format($percentage, 2) . '%',
            $project->donor_count ?? 0,
        ];
    }
}
