<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Imports\DepartmentsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class DepartmentUpload extends Component
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
            Excel::import(new DepartmentsImport, $this->upload);
            $this->successMessage = 'Departments imported successfully!';
            $this->dispatch('departmentsUpdated'); // To refresh the table
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorRows = [];
            foreach ($failures as $failure) {
                $errorRows[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            $this->errorMessage = 'Validation error(s):<br>' . implode('<br>', $errorRows);
        } catch (\Exception $e) {
            Log::error('Department Import Error: ' . $e->getMessage());
            $this->errorMessage = 'An unexpected error occurred during import.';
        }
        
        $this->importing = false;
        $this->importFinished = true;
        $this->reset('upload');
    }

    public function render()
    {
        return view('livewire.admin.department-upload');
    }
}
