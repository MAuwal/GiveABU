<?php

namespace App\Exports;

use App\Models\Donation;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProjectDonationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $projectId;
    protected $projectName;

    public function __construct($projectId, $projectName = null)
    {
        $this->projectId = $projectId;
        $this->projectName = $projectName;
    }

    public function query()
    {
        if ($this->projectId === 'null' || $this->projectId === null) {
            return Donation::query()
                ->whereNull('project_id')
                ->with(['donor.faculty', 'donor.department'])
                ->orderBy('created_at', 'desc');
        }

        return Donation::query()
            ->where('project_id', $this->projectId)
            ->with(['donor.faculty', 'donor.department'])
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Date',
            'Donor Name',
            'Email',
            'Phone',
            'Faculty',
            'Department',
            'Entry Year',
            'Amount (₦)',
            'Status',
            'Payment Reference',
            'Project',
        ];
    }

    public function map($donation): array
    {
        $donor = $donation->donor;
        
        return [
            $donation->created_at->format('Y-m-d H:i:s'),
            $donor ? ($donor->surname . ' ' . $donor->name) : 'Anonymous',
            $donor ? $donor->email : '',
            $donor ? $donor->phone : '',
            $donor && $donor->faculty ? ($donor->faculty->current_name ?? $donor->faculty->name) : '',
            $donor && $donor->department ? ($donor->department->current_name ?? $donor->department->name) : '',
            $donor ? $donor->entry_year : '',
            number_format($donation->amount, 2, '.', ''),
            ucfirst($donation->status),
            $donation->payment_reference,
            $this->projectName ?? ($donation->project ? $donation->project->project_title : 'Endowment Project'),
        ];
    }
}
