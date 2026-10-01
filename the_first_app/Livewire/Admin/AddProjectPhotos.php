<?php

namespace App\Livewire\Admin;

use App\Models\Project;
use App\Models\ProjectPhoto;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class AddProjectPhotos extends Component
{
    use WithFileUploads;

    public $showModal = false;
    public Project $project;
    public $photos = [];
    public $titles = []; // Titles for new uploads
    public $descriptions = []; // Descriptions for new uploads
    public $editingPhotoId = null;
    public $editingTitle = '';
    public $editingDescription = '';
    public $refreshKey = 0; // Force refresh trigger

    protected $rules = [
        'photos.*' => 'required|image|max:5120', // 5MB max per image
        'titles.*' => 'nullable|string|max:255',
        'descriptions.*' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'photos.*.required' => 'Please select at least one photo.',
        'photos.*.image' => 'Each file must be an image.',
        'photos.*.max' => 'Each image size must not exceed 5MB.',
    ];

    public function mount()
    {
        $this->project->load('photos');
    }

    public function open()
    {
        $this->resetForm();
        $this->project->load('photos'); // Ensure photos are loaded
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->photos = [];
        $this->titles = [];
        $this->descriptions = [];
        $this->editingPhotoId = null;
        $this->editingTitle = '';
        $this->editingDescription = '';
        $this->resetValidation();
    }

    public function updatedPhotos()
    {
        // Initialize titles and descriptions for new photos
        foreach ($this->photos as $index => $photo) {
            if (!isset($this->titles[$index])) {
                $this->titles[$index] = '';
            }
            if (!isset($this->descriptions[$index])) {
                $this->descriptions[$index] = '';
            }
        }
    }

    public function savePhotos()
    {
        $this->validate();

        try {
            Storage::disk('public')->makeDirectory('projects/photos');
            foreach ($this->photos as $index => $photo) {
                $photoPath = $photo->store('projects/photos', 'public');
                if ($photoPath === false) {
                    throw new \RuntimeException('Failed to save photo ' . ($index + 1) . '. Check server storage permissions.');
                }

                ProjectPhoto::create([
                    'project_id' => $this->project->id,
                    'body_image' => $photoPath,
                    'title' => $this->titles[$index] ?? null,
                    'description' => $this->descriptions[$index] ?? null,
                ]);
            }

            $this->reset(['photos', 'titles', 'descriptions']);
            $this->project->refresh();
            $this->refreshKey++;
            
            $this->dispatch('photos-updated', projectId: $this->project->id);
            session()->flash('message', 'Photos added successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error adding photos: ' . $e->getMessage());
        }
    }

    public function editPhotoDetails($photoId)
    {
        $photo = ProjectPhoto::findOrFail($photoId);
        $this->editingPhotoId = $photoId;
        $this->editingTitle = $photo->title;
        $this->editingDescription = $photo->description;
    }

    public function cancelEdit()
    {
        $this->editingPhotoId = null;
        $this->editingTitle = '';
        $this->editingDescription = '';
    }

    public function updatePhotoDetails()
    {
        $this->validate([
            'editingTitle' => 'nullable|string|max:255',
            'editingDescription' => 'nullable|string|max:1000',
        ]);

        try {
            $photo = ProjectPhoto::findOrFail($this->editingPhotoId);
            $photo->update([
                'title' => $this->editingTitle,
                'description' => $this->editingDescription
            ]);
            
            $this->editingPhotoId = null;
            $this->editingTitle = '';
            $this->editingDescription = '';
            $this->project->refresh();
            session()->flash('message', 'Photo details updated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating photo details: ' . $e->getMessage());
        }
    }

    public function deletePhoto($photoId)
    {
        try {
            $photo = ProjectPhoto::findOrFail($photoId);
            
            if ($photo->body_image) {
                Storage::disk('public')->delete($photo->body_image);
            }
            
            $photo->delete();
            $this->project->refresh();
            $this->refreshKey++;
            session()->flash('message', 'Photo deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting photo: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Always reload photos to ensure we have the latest data
        if ($this->project->exists) {
            $this->project->load('photos');
        }
        
        return view('livewire.admin.add-project-photos');
    }
}