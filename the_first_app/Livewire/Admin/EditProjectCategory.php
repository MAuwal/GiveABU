<?php

namespace App\Livewire\Admin;

use App\Models\ProjectCategory;
use App\Models\Department;
use App\Models\Faculty;
use Livewire\Component;
use Livewire\Attributes\On;

class EditProjectCategory extends Component
{
    public $showModal = false;
    public $categoryId;
    public $name;
    public $description;
    public $department_id;
    public $faculty_id;
    public $departments;
    public $faculties;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'department_id' => 'nullable|exists:departments,id',
        'faculty_id' => 'nullable|exists:faculties,id',
    ];

    #[On('open-edit-category-modal')]
    public function openModal($categoryId = null)
    {
        // Handle event data - Livewire passes data as array
        if (is_array($categoryId) && isset($categoryId['categoryId'])) {
            $categoryId = $categoryId['categoryId'];
        }
        
        if (!$categoryId) {
            return;
        }
        
        $this->categoryId = $categoryId;
        $category = ProjectCategory::findOrFail($categoryId);
        
        $this->name = $category->name;
        $this->description = $category->description;
        $this->department_id = $category->department_id;
        $this->faculty_id = $category->faculty_id;
        $this->departments = Department::all();
        $this->faculties = Faculty::all();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['categoryId', 'name', 'description', 'department_id', 'faculty_id']);
    }

    public function updateCategory()
    {
        // Update validation rule to ignore current category
        $this->rules['name'] = 'required|string|max:255|unique:project_categories,name,' . $this->categoryId;
        
        $this->department_id = $this->department_id ?: null;
        $this->faculty_id = $this->faculty_id ?: null;

        $this->validate();

        $category = ProjectCategory::findOrFail($this->categoryId);
        $category->update([
            'name' => $this->name,
            'description' => $this->description,
            'department_id' => $this->department_id ?: null,
            'faculty_id' => $this->faculty_id ?: null,
        ]);

        session()->flash('message', 'Category updated successfully.');
        $this->dispatch('category-updated');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.admin.edit-project-category');
    }
}

