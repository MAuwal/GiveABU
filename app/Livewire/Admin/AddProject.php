<?php

namespace App\Livewire\Admin;

use App\Models\Project;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class AddProject extends Component
{
    use WithFileUploads;

    public $showModal = false;
    public $project_title = '';
    public $project_description = '';
    public $icon_image;
    public $target = '';
    public $status = 'active';
    public $editingProjectId = null;
    public $existing_icon_image = null;

    protected $rules = [
        'project_title' => 'required|string|max:255',
        'project_description' => 'required|string',
        'target' => 'nullable|numeric|min:0',
        'status' => 'required|in:active,closed',
        'icon_image' => 'nullable|image|max:5048', // 2MB max
    ];

    protected $messages = [
        'project_title.required' => 'Project title is required.',
        'project_description.required' => 'Project description is required.',
        'icon_image.image' => 'The file must be an image.',
        'icon_image.max' => 'The image size must not exceed 2MB.',
    ];

    #[On('open-add-project-modal')]
    public function openModal(...$params)
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->project_title = '';
        $this->project_description = '';
        $this->target = '';
        $this->status = 'active';
        $this->icon_image = null;
        $this->existing_icon_image = null;
        $this->editingProjectId = null;
        $this->resetValidation();
    }

    #[On('edit-project')]
    public function editProject($projectId)
    {
        $project = Project::findOrFail($projectId);

        $this->editingProjectId = $project->id;
        $this->project_title = $project->project_title;
        $this->project_description = $project->project_description;
        $this->target = $project->target;
        $this->status = $project->status;
        $this->existing_icon_image = $project->icon_image;
        $this->icon_image = null; // reset new upload

        $this->showModal = true;
    }

    public function updatedIconImage(): void
    {
        Log::info('Livewire updatedIconImage fired', [
            'received'  => !is_null($this->icon_image),
            'class'     => $this->icon_image ? get_class($this->icon_image) : 'null',
            'tmp_dir'   => storage_path('app/private/livewire-tmp'),
            'tmp_exists'=> is_dir(storage_path('app/private/livewire-tmp')),
            'tmp_write' => is_writable(storage_path('app/private/livewire-tmp')),
        ]);
    }

    public function saveProject()
    {
        Log::info('saveProject method called.');

        // $validatedData = $this->validate();
        // Log::info('Validation successful.', $validatedData);

        try {
            $iconImagePath = $this->existing_icon_image;

            if ($this->icon_image) {
                Log::info('Icon image present. Storing...');
                Storage::disk('public')->makeDirectory('projects/icons');
                $iconImagePath = $this->icon_image->store('projects/icons', 'public');
                if ($iconImagePath === false) {
                    Log::error('Icon image store() returned false — check directory permissions for storage/app/public/projects/icons');
                    throw new \RuntimeException('Failed to save image. Check server storage permissions.');
                }
                Log::info('Icon image stored at: ' . $iconImagePath);
            }

            $projectData = [
                'project_title' => $this->project_title,
                'project_description' => $this->project_description,
                'target' => $this->target ?: 0,
                'status' => $this->status,
                'icon_image' => $iconImagePath,
            ];

            if ($this->editingProjectId) {
                $project = Project::findOrFail($this->editingProjectId);
                $project->update($projectData);
                Log::info('Project updated successfully with data:', $project->toArray());
                session()->flash('message', 'Project updated successfully!');
            } else {
                Log::info('Creating project with data:', $projectData);
                $project = Project::create($projectData);
                Log::info('Project saved successfully with data:', $project->toArray());
                Log::info('Project created successfully.', [
                    'project_id' => $project->id,
                    'icon_image' => $project->icon_image,
                    'icon_image_url' => $project->icon_image_url
                ]);
                session()->flash('message', 'Project added successfully!');
            }

            $this->closeModal();
            $this->resetForm();
            $this->dispatch('project-added');
            
        } catch (\Exception $e) {
            Log::error('Error adding project: ' . $e->getMessage());
            session()->flash('error', 'Error adding project: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.add-project');
    }
} 