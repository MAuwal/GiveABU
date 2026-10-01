<?php

namespace App\Livewire\Admin;

use App\Models\Project;
use App\Models\ProjectDetail;
use Livewire\Component;

class ManageProjectDetails extends Component
{
    public $showModal = false;
    public $project;
    public $background;
    public $challenges;
    public $proposed_interventions;
    public $expected_outcomes;
    public $beneficiaries;
    public $budget_estimates;

    protected $listeners = ['manage-project-details' => 'openModal'];

    public function openModal($projectId)
    {
        $this->project = Project::findOrFail($projectId);
        $details = $this->project->details;

        if ($details) {
            $this->background = $details->background;
            $this->challenges = $details->challenges;
            $this->proposed_interventions = $details->proposed_interventions;
            $this->expected_outcomes = $details->expected_outcomes;
            $this->beneficiaries = $details->beneficiaries;
            $this->budget_estimates = $details->budget_estimates;
        } else {
            $this->reset(['background', 'challenges', 'proposed_interventions', 'expected_outcomes', 'beneficiaries', 'budget_estimates']);
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'background' => 'nullable|string',
            'challenges' => 'nullable|string',
            'proposed_interventions' => 'nullable|string',
            'expected_outcomes' => 'nullable|string',
            'beneficiaries' => 'nullable|string',
            'budget_estimates' => 'nullable|string',
        ]);

        ProjectDetail::updateOrCreate(
            ['project_id' => $this->project->id],
            [
                'background' => $this->background,
                'challenges' => $this->challenges,
                'proposed_interventions' => $this->proposed_interventions,
                'expected_outcomes' => $this->expected_outcomes,
                'beneficiaries' => $this->beneficiaries,
                'budget_estimates' => $this->budget_estimates,
            ]
        );

        $this->showModal = false;
        // Optional: Dispatch a notification event if you have a notification system
        // $this->dispatch('notify', 'Project details saved successfully!');
    }

    public function render()
    {
        return view('livewire.admin.manage-project-details');
    }
}
