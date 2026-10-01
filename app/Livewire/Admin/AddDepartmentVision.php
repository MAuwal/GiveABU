<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\DepartmentVision;
use App\Models\FacultyVision;
use Livewire\Component;

class AddDepartmentVision extends Component
{
    public $department_id;
    public $faculty_vision_id;
    public $name;
    public $start_year;
    public $end_year;
    public $showModal = false;
    public $departmentName;
    public $facultyVisions = [];

    protected $listeners = ['showAddDepartmentVisionModal'];

    public function showAddDepartmentVisionModal($departmentId)
    {
        $this->department_id = $departmentId;
        $department = Department::with('faculty')->findOrFail($departmentId);
        $this->departmentName = $department->current_name;
        $this->facultyVisions = FacultyVision::where('faculty_id', $department->faculty_id)->get();
        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
        $this->reset();
    }

    public function save()
    {
        $this->validate([
            'faculty_vision_id' => 'required|exists:faculty_visions,id',
            'name' => 'required|string',
            'start_year' => 'required|date',
            'end_year' => 'required|date|after_or_equal:start_year',
        ]);

        DepartmentVision::create([
            'department_id' => $this->department_id,
            'faculty_version_id' => $this->faculty_vision_id,
            'name' => $this->name,
            'start_year' => $this->start_year,
            'end_year' => $this->end_year,
        ]);

        $this->close();
        $this->dispatch('visionAdded');
        session()->flash('message', 'Department vision added successfully.');
    }

    public function render()
    {
        return view('livewire.admin.add-department-vision');
    }
}
