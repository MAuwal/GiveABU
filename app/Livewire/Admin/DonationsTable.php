<?php

namespace App\Livewire\Admin;

use App\Models\Project;
use App\Models\Donation;
use Livewire\Component;
use Livewire\WithPagination;

class DonationsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $perPageModal = 10; // Per page for modal donations table
    public $showDetailsModal = false;
    public $selectedProjectId = null; // Store only the ID, not the whole object
    public $modalPage = 1; // Current page for modal
    public $totalRaised = 0; // Total amount raised for selected project

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showProjectDetails($projectId)
    {
        $this->modalPage = 1; // Reset to first page when opening modal
        $this->selectedProjectId = $projectId;
        
        if ($projectId === null) {
            // Calculate total raised for Endowment Project
            $this->totalRaised = Donation::whereNull('project_id')->sum('amount');
        } else {
            // Calculate total raised for specific project
            $project = Project::find($projectId);
            $this->totalRaised = $project ? $project->donations()->sum('amount') : 0;
        }
        
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedProjectId = null;
        $this->modalPage = 1;
        $this->totalRaised = 0;
    }

    public function updatedPerPageModal()
    {
        $this->modalPage = 1; // Reset to first page when changing per page
    }

    public function gotoModalPage($page)
    {
        $this->modalPage = $page;
    }

    public function exportProjects()
    {
        $projectsWithEndowment = $this->getProjectsData();
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProjectsOverviewExport($projectsWithEndowment), 
            'donations_overview_' . date('Y-m-d') . '.xlsx'
        );
    }

    public function exportModalDonations()
    {
        if (!$this->showDetailsModal) {
            return;
        }

        $projectId = $this->selectedProjectId;
        $projectName = 'Endowment Project';

        if ($projectId !== null) {
            $project = Project::find($projectId);
            $projectName = $project ? $project->project_title : 'Unknown Project';
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProjectDonationsExport($projectId, $projectName),
            'donations_' . \Illuminate\Support\Str::slug($projectName) . '_' . date('Y-m-d') . '.xlsx'
        );
    }

    private function getProjectsData()
    {
        $search = $this->search;

        // Get all projects
        $projectsQuery = Project::query()
            ->when($search, function ($query) use ($search) {
                $query->where('project_title', 'like', "%{$search}%")
                      ->orWhere('project_description', 'like', "%{$search}%");
            });

        $projects = $projectsQuery->orderBy('created_at', 'desc')->get();

        // Calculate raised amount and donor count for each project
        $projectsWithRaised = $projects->map(function ($project) {
            $raised = Donation::where('project_id', $project->id)
                ->whereIn('status', ['success', 'paid', 'completed'])
                ->sum('amount');
            $donorCount = Donation::where('project_id', $project->id)
                ->whereIn('status', ['success', 'paid', 'completed'])
                ->distinct('donor_id')
                ->count('donor_id');
            $project->raised_amount = $raised;
            $project->donor_count = $donorCount;
            return $project;
        });

        // Get total raised and donor count for null project_id donations (Endowment Project)
        $endowmentRaised = Donation::whereNull('project_id')
            ->whereIn('status', ['success', 'paid', 'completed'])
            ->sum('amount');
        $endowmentDonorCount = Donation::whereNull('project_id')
            ->whereIn('status', ['success', 'paid', 'completed'])
            ->distinct('donor_id')
            ->count('donor_id');

        // Create a collection that includes Endowment Project if there are null donations
        $projectsWithEndowment = collect();

        // Add Endowment Project entry if there are donations with null project_id
        if ($endowmentRaised > 0) {
            $endowmentProject = (object) [
                'id' => null,
                'project_title' => 'Endowment Project',
                'project_description' => 'General endowment donations',
                'target' => null,
                'raised' => null,
                'raised_amount' => $endowmentRaised,
                'donor_count' => $endowmentDonorCount,
                'created_at' => null,
            ];
            
            // Apply search filter for Endowment Project
            if (!$search || 
                stripos('Endowment Project', $search) !== false || 
                stripos('General endowment donations', $search) !== false) {
                $projectsWithEndowment->push($endowmentProject);
            }
        }

        // Add all regular projects
        foreach ($projectsWithRaised as $project) {
            $projectsWithEndowment->push($project);
        }

        return $projectsWithEndowment;
    }

    public function render()
    {
        $projectsWithEndowment = $this->getProjectsData();

        // Paginate manually
        $currentPage = request()->get('page', 1);
        $items = $projectsWithEndowment->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
        $total = $projectsWithEndowment->count();

        // Create paginator manually
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Compute selectedProject and selectedDonations for modal
        $selectedProject = null;
        $selectedDonations = collect();
        
        if ($this->showDetailsModal && $this->selectedProjectId !== null) {
            // Regular project
            $selectedProject = Project::find($this->selectedProjectId);
            $selectedDonations = $selectedProject ? $selectedProject->donations()
                ->with(['donor.faculty', 'donor.department'])
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPageModal, ['*'], 'modalPage', $this->modalPage) : collect();
        } elseif ($this->showDetailsModal && $this->selectedProjectId === null) {
            // Endowment Project
            $selectedProject = (object) [
                'id' => null,
                'project_title' => 'Endowment Project',
                'project_description' => 'General endowment donations',
                'target' => null,
                'raised' => null,
            ];
            $selectedDonations = Donation::whereNull('project_id')
                ->with(['donor.faculty', 'donor.department'])
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPageModal, ['*'], 'modalPage', $this->modalPage);
        }

        return view('livewire.admin.donations-table', [
            'projects' => $paginator,
            'selectedProject' => $selectedProject,
            'selectedDonations' => $selectedDonations,
        ]);
    }
}
