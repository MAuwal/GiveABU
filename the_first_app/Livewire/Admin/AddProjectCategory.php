<?php

namespace App\Livewire\Admin;

use App\Models\ProjectCategory;
use App\Models\Department;
use App\Models\Faculty;
use Livewire\Component;
use Livewire\Attributes\On;

class AddProjectCategory extends Component
{
    public $showModal = false;
    public $name;
    public $description;
    public $department_id;
    public $faculty_id;
    public $departments;
    public $faculties;

    protected $rules = [
        'name' => 'required|string|max:255|unique:project_categories,name',
        'description' => 'nullable|string',
        'department_id' => 'nullable|exists:departments,id',
        'faculty_id' => 'nullable|exists:faculties,id',
    ];

    #[On('open-add-category-modal')]
    public function openModal()
    {
        $this->reset();
        $this->resetValidation();
        $this->departments = Department::all();
        $this->faculties = Faculty::all();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function saveCategory()
    {
        $this->department_id = $this->department_id ?: null;
        $this->faculty_id = $this->faculty_id ?: null;

        $this->validate();

        ProjectCategory::create([
            'name' => $this->name,
            'description' => $this->description,
            'department_id' => $this->department_id ?: null,
            'faculty_id' => $this->faculty_id ?: null,
        ]);

        session()->flash('message', 'Category added successfully.');
        $this->dispatch('category-added');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.admin.add-project-category');
    }
}

