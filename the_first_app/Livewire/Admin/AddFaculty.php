<?php

namespace App\Livewire\Admin;

use App\Models\Faculty;
use Livewire\Component;
use Livewire\Attributes\On;

class AddFaculty extends Component
{
    public $showModal = false;
    public $current_name = '';
    public $started_at = '';
    public $ended_at = '';

    protected $rules = [
        'current_name' => 'required|string|max:255|unique:faculties,current_name',
        'started_at' => 'required|integer|min:1900|max:2100',
        'ended_at' => 'nullable|integer|min:1900|max:2100|gte:started_at',
    ];

    protected $messages = [
        'current_name.required' => 'Faculty name is required.',
        'current_name.unique' => 'A faculty with this name already exists.',
        'current_name.max' => 'Faculty name cannot exceed 255 characters.',
        'started_at.required' => 'Start year is required.',
        'started_at.integer' => 'Start year must be a valid year.',
        'started_at.min' => 'Start year must be 1900 or later.',
        'started_at.max' => 'Start year cannot be later than 2100.',
        'ended_at.integer' => 'End year must be a valid year.',
        'ended_at.gte' => 'End year must be equal to or later than start year.',
    ];

    public function mount()
    {
        $this->showModal = false;
    }

    #[On('openModal')]
    public function openModal()
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
        $this->reset(['current_name', 'started_at', 'ended_at']);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        try {
            Faculty::create([
                'current_name' => $this->current_name,
                'started_at' => $this->started_at,
                'ended_at' => $this->ended_at ?: null,
            ]);

            $this->closeModal();
            $this->dispatch('facultyAdded'); // Emit event to refresh parent component
            session()->flash('message', 'Faculty added successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Error adding faculty: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.add-faculty');
    }
}
