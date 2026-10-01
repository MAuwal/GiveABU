<?php

namespace App\Livewire\Admin;

use App\Models\Donor;
use App\Models\Faculty;
use App\Models\Department;
use Livewire\Component;
use Livewire\Attributes\On;

class AddDonor extends Component
{
    public $showModal = false;

    // Form fields
    public $surname;
    public $name;
    public $other_name;
    public $gender;
    public $reg_number;
    public $email;
    public $phone;
    public $address;
    public $nationality;
    public $state;
    public $lga;
    public $entry_year;
    public $graduation_year;
    public $faculty_id;
    public $department_id;

    public $faculties;
    public $departments = [];

    protected $rules = [
        'surname' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'other_name' => 'nullable|string|max:255',
        'gender' => 'nullable|in:male,female',
        'reg_number' => 'nullable|string|max:100',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:500',
        'nationality' => 'nullable|string|max:100',
        'state' => 'nullable|string|max:100',
        'lga' => 'nullable|string|max:100',
        'entry_year' => 'nullable|digits:4|integer|min:1900',
        'graduation_year' => 'required|digits:4|integer|min:1900',
        'faculty_id' => 'required|exists:faculties,id',
        'department_id' => 'required|exists:departments,id',
    ];

    public function mount()
    {
        $this->faculties = Faculty::orderBy('current_name')->get();
    }

    #[On('open-add-donor-modal')]
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function updatedFacultyId($value)
    {
        if ($value) {
            $this->departments = Department::where('faculty_id', $value)->orderBy('current_name')->get();
            $this->department_id = null; // Reset department selection
        } else {
            $this->departments = [];
        }
    }

    public function saveDonor()
    {
        $this->validate();

        Donor::create([
            'surname' => $this->surname,
            'name' => $this->name,
            'other_name' => $this->other_name,
            'gender' => $this->gender,
            'reg_number' => $this->reg_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'nationality' => $this->nationality,
            'state' => $this->state,
            'lga' => $this->lga,
            'entry_year' => $this->entry_year,
            'graduation_year' => $this->graduation_year,
            'faculty_id' => $this->faculty_id,
            'department_id' => $this->department_id,
            'donor_type' => 'addressable_alumni',
        ]);

        session()->flash('message', 'Donor added successfully.');
        $this->dispatch('donor-added');
        $this->closeModal();
    }
    
    public function resetForm()
    {
        $this->reset(['surname','name','other_name','gender','reg_number','email','phone','address','nationality','state','lga','entry_year','graduation_year','faculty_id','department_id']);
        $this->departments = [];
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.add-donor');
    }
} 