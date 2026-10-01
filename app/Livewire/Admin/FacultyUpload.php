<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Imports\FacultiesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class FacultyUpload extends Component
{
    use WithFileUploads;

    public $upload;
    public $showModal = false;
    public $importing = false;
    public $importFinished = false;
    public $successMessage = '';
    public $errorMessage = '';

    protected $rules = [
        'upload' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB Max
    ];

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->reset(['upload', 'importing', 'importFinished', 'successMessage', 'errorMessage']);
    }

    public function import()
    {
        $this->validate();

        $this->importing = true;
        
        try {
            Excel::import(new FacultiesImport, $this->upload);
            $this->successMessage = 'Faculties imported successfully!';
            $this->dispatch('facultiesUpdated'); // To refresh the table
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $this->errorMessage = 'Validation error: ' . $e->failures()[0]->errors()[0];
        } catch (\Exception $e) {
            Log::error('Faculty Import Error: ' . $e->getMessage());
            $this->errorMessage = 'An unexpected error occurred during import.';
        }
        
        $this->importing = false;
        $this->importFinished = true;
        $this->reset('upload');
    }

    public function render()
    {
        return view('livewire.admin.faculty-upload');
    }
}
