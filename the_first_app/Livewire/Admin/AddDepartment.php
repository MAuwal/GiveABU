<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Faculty;
use Livewire\Component;

class AddDepartment extends Component
{
    public $showModal = false;
    public $faculty;
    public $departmentName = '';
    public $started_at = '';
    public $ended_at = '';
    public $successMessage = '';
    public $errorMessage = '';

    protected $listeners = ['showAddDepartmentModal'];

    public function showAddDepartmentModal(Faculty $faculty)
    {
        $this->reset();
        $this->faculty = $faculty;
        $this->showModal = true;
    }

    public function rules()
    {
        return [
            'departmentName' => 'required|string|max:255|unique:departments,name,NULL,id,faculty_id,' . $this->faculty->id,
            'started_at' => 'required|integer|min:1900|max:2100',
            'ended_at' => 'nullable|integer|min:1900|max:2100|gte:started_at',
        ];
    }

    public function messages()
    {
        return [
            'departmentName.unique' => 'This department already exists in this faculty.',
            'started_at.required' => 'Start year is required.',
            'started_at.integer' => 'Start year must be a valid year.',
            'started_at.min' => 'Start year must be 1900 or later.',
            'started_at.max' => 'Start year cannot be later than 2100.',
            'ended_at.integer' => 'End year must be a valid year.',
            'ended_at.gte' => 'End year must be equal to or later than start year.',
        ];
    }

    public function saveDepartment()
    {
        $this->validate();

        try {
            Department::create([
                'current_name' => $this->departmentName,
                'faculty_id' => $this->faculty->id,
                'started_at' => $this->started_at,
                'ended_at' => $this->ended_at ?: null,
            ]);

            $this->successMessage = 'Department added successfully.';
            $this->dispatch('departmentAdded'); // To refresh data if needed
            $this->reset(['departmentName', 'started_at', 'ended_at']);
            
            // Keep the modal open to add another or show success
            // $this->showModal = false; 

        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred while adding the department.';
            // Log::error('Add Department Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.add-department');
    }
}
