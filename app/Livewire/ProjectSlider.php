<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;

class ProjectSlider extends Component
{
    public $projects = [];

    public function mount()
    {
        $this->projects = Project::with('photos')->orderBy('created_at', 'desc')->get();
    }

    public function render()
    {
        return view('livewire.project-slider');
    }
}
