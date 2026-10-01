<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Faculty;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class DepartmentsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $current_name = '';
    public $faculty_id = '';
    public $showModal = false;

    protected $rules = [
        'current_name' => 'required|string|max:255|unique:departments,current_name',
        'faculty_id' => 'required|exists:faculties,id',
    ];

    protected $messages = [
        'current_name.required' => 'Department name is required.',
        'current_name.unique' => 'A department with this name already exists.',
        'current_name.max' => 'Department name cannot exceed 255 characters.',
        'faculty_id.required' => 'Please select a faculty.',
        'faculty_id.exists' => 'Selected faculty does not exist.',
    ];

    public function initComponent()
    {
        // Force Livewire to initialize properly
        $this->current_name = '';
        $this->faculty_id = '';
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->reset(['current_name', 'faculty_id']);
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['current_name', 'faculty_id']);
        $this->resetValidation();
    }

    public function saveDepartment()
    {
        $this->validate();

        try {
            Department::create([
                'current_name' => $this->current_name,
                'faculty_id' => $this->faculty_id,
            ]);

            $this->reset(['current_name', 'faculty_id']);
            $this->resetValidation();
            $this->showModal = false;
            
            session()->flash('message', 'Department added successfully!');
            
            // Refresh the table
            $this->resetPage();

        } catch (\Exception $e) {
            session()->flash('error', 'Error adding department: ' . $e->getMessage());
        }
    }

    #[On('departmentsUpdated')] 
    #[On('departmentAdded')]
    #[On('refreshDepartments')]
    public function render()
    {
        $departments = Department::with(['faculty', 'visions'])
            ->where('current_name', 'like', '%'.$this->search.'%')
            ->orWhereHas('faculty', function ($query) {
                $query->where('current_name', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        $faculties = Faculty::orderBy('current_name')->get();

        return view('livewire.admin.departments-table', [
            'departments' => $departments,
            'faculties' => $faculties
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
