<?php

namespace App\Livewire\Home;

use Livewire\Component;
use App\Models\Project;

class Projects extends Component
{
    public function render()
    {
        // Get active projects for the carousel
        $projects = Project::whereNotNull('project_title')
                          ->whereNotNull('project_description')
                          ->whereNotNull('icon_image')
                          ->limit(3) // Limit to 3 projects for carousel
                          ->get();

        return view('livewire.home.projects', compact('projects'));
    }
}
