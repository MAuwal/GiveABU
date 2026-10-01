<?php

namespace App\Livewire\Admin;

use App\Models\Faculty;
use App\Models\FacultyVision;
use Livewire\Component;

class AddFacultyVision extends Component
{
    public $faculty_id;
    public $name;
    public $start_year;
    public $end_year;
    public $showModal = false;
    public $facultyName;

    protected $listeners = ['showAddVisionModal'];

    public function showAddVisionModal($facultyId)
    {
        $this->faculty_id = $facultyId;
        $faculty = Faculty::findOrFail($facultyId);
        $this->facultyName = $faculty->current_name;
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
            'name' => 'required|string',
            'start_year' => 'required|date',
            'end_year' => 'required|date|after_or_equal:start_year',
        ]);

        FacultyVision::create([
            'faculty_id' => $this->faculty_id,
            'name' => $this->name,
            'start_year' => $this->start_year,
            'end_year' => $this->end_year,
        ]);

        $this->close();
        $this->dispatch('visionAdded');
        // Optionally, you can add a success message
        session()->flash('message', 'Faculty vision added successfully.');
    }

    public function render()
    {
        return view('livewire.admin.add-faculty-vision');
    }
}
