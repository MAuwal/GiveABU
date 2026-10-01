<?php

namespace App\Livewire\Admin;

use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;

class ViewProjectDetails extends Component
{
    public $project;
    public $photos = [];
    public $otherProjects;

    public function mount(Project $project)
    {
        $this->project = $project->load('details');
        
        // Initialize photos array
        $this->photos = [];

        // Add icon image as the first photo if it exists
        if ($project->icon_image) {
            $this->photos[] = [
                'url' => $project->icon_image_url ?? asset('storage/' . $project->icon_image),
                'description' => $project->project_description, // Use project description for the main icon
                'title' => $project->project_title // Use project title for the main icon
            ];
        }

        // Add other project photos
        foreach ($project->photos as $photo) {
            $this->photos[] = [
                'url' => $photo->image_url,
                'description' => $photo->description,
                'title' => $photo->title
            ];
        }

        // Fetch other projects
        $this->otherProjects = Project::where('id', '!=', $project->id)
            ->where('status', 'active')
            ->inRandomOrder()
            ->get();
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.view-project-details');
    }
}