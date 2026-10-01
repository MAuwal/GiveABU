<?php

namespace App\Livewire\Admin;

use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectsManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 9;
    public $statusFilter = 'active';
    public $showDonationsModal = false;
    public $showAddProjectModal = false;
    public $selectedProject = null;
    public $selectedDonations = [];
    public $confirmingDeleteId = null;
    
    protected $listeners = ['project-added' => 'refreshProjects'];
    
    public function refreshProjects()
    {
        $this->resetPage();
    }
    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // #[On('project-added')]
    // public function refreshTable()
    // {
    //     // This will trigger a re-render
    // }

    public function showDonations($projectId)
    {
        $project = \App\Models\Project::with('photos')->find($projectId);
        $this->selectedProject = $project;
        $this->selectedDonations = $project ? $project->donations()->with('donor')->orderBy('created_at', 'desc')->get() : [];
        $this->showDonationsModal = true;
    }

    public function editProject($projectId)
    {
        $this->dispatch('edit-project', projectId: $projectId);
    }

    public function showAddProjectModal()
    {
        $this->showAddProjectModal = true;
    }

    public function closeAddProjectModal()
    {
        $this->showAddProjectModal = false;
    }

    public function confirmDelete($projectId): void
    {
        $this->confirmingDeleteId = $projectId;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function deleteProject($projectId): void
    {
        $project = Project::find($projectId);
        if (!$project) {
            $this->confirmingDeleteId = null;
            return;
        }

        if ($project->icon_image) {
            Storage::disk('public')->delete($project->icon_image);
        }

        $project->delete();
        $this->confirmingDeleteId = null;
        session()->flash('message', 'Project deleted successfully.');
    }

    public function loadMore()
    {
        $this->perPage += 9;
    }


    public function render()
    {
        $projects = Project::query()
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('project_title', 'like', '%' . $this->search . '%')
                      ->orWhere('project_description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->withCount('photos')
            ->withSum(['donations as calculated_raised' => function($query) {
                $query->whereIn('status', ['success', 'paid', 'completed']);
            }], 'amount')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.projects-manager', [
            'projects' => $projects,
            'showAddProjectModal' => $this->showAddProjectModal,
        ]);
    }
}
