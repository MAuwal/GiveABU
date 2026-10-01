<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class ProjectsPage extends Component
{
    public function openAddProjectModal()
    {
        $this->dispatch('open-add-project-modal');
    }

    public function openProjectUploadModal()
    {
        $this->dispatch('open-project-upload-modal');
    }

    public function render()
    {
        return view('livewire.admin.projects-page');
    }
} 