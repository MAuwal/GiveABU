<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Project;

class ProjectStatusToggle extends Component
{
    public $projects = [];

    public function mount()
    {
        $this->loadProjects();
    }

    public function loadProjects()
    {
        $this->projects = Project::withTrashed()
            ->with(['photos', 'donations'])
            ->get()
            ->map(function ($project) {
                $project->total_donations = $project->donations->sum('amount');
                return $project;
            });
    }

    public function toggleStatus($projectId)
    {
        $project = Project::withTrashed()->findOrFail($projectId);

        if ($project->trashed()) {
            $project->restore();
        } else {
            $project->delete();
        }

        // Reload data so UI updates
        $this->loadProjects();

        // Livewire v3 event
        $this->dispatch('notify', message: 'Status updated');
    }

    public function render()
    {
        return view('livewire.admin.project-status-toggle');
    }
}