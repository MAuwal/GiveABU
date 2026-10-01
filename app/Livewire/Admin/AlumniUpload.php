<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Imports\DonorsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class AlumniUpload extends Component
{
    use WithFileUploads;

    public $upload;
    public $showModal = false;
    public $importing = false;
    public $importFinished = false;
    public $successMessage = '';
    public $errorMessage = '';
    public $failures = [];
    public $importedCount = 0;
    public $failedCount = 0;
    public $skippedCount = 0;

    protected $rules = [
        'upload' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB Max
    ];

    public function openModal()
    {
        $this->reset(['upload', 'importing', 'importFinished', 'successMessage', 'errorMessage', 'failures', 'importedCount', 'failedCount', 'skippedCount']);
        $this->showModal = true;
    }

    public function import()
    {
        $this->validate();

        $this->importing = true;
        $this->importFinished = false;
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->failures = [];
        $this->importedCount = 0;
        $this->failedCount = 0;
        $this->skippedCount = 0;

        try {
            $import = new DonorsImport;
            Excel::import($import, $this->upload);

            $this->importFinished = true;
            
            // Get counts from import
            $this->importedCount = $import->getImportedCount();
            $this->skippedCount = $import->getSkippedCount();
            
            // Get failures if any
            if ($import->failures()->isNotEmpty()) {
                $this->failures = collect($import->failures())->map(function($failure) {
                    return [
                        'row' => $failure->row(),
                        'attribute' => $failure->attribute(),
                        'errors' => $failure->errors(),
                        'values' => $failure->values()
                    ];
                })->toArray();
                $this->failedCount = count($this->failures);
                $this->errorMessage = "Upload completed with {$this->failedCount} validation errors. {$this->importedCount} records imported, {$this->skippedCount} records skipped.";
            } else {
                if ($this->importedCount > 0) {
                    $this->successMessage = "Successfully imported {$this->importedCount} alumni records!";
                    if ($this->skippedCount > 0) {
                        $this->successMessage .= " {$this->skippedCount} records were skipped (duplicates or invalid data).";
                    }
                } else {
                    $this->errorMessage = "No records were imported. {$this->skippedCount} records were skipped (duplicates or invalid data).";
                }
            }
            
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $this->failures = collect($e->failures())->map(function($failure) {
                return [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values()
                ];
            })->toArray();
            $this->failedCount = count($this->failures);
            $this->errorMessage = "Validation errors found in {$this->failedCount} rows. Please check the details below.";
        } catch (\Exception $e) {
            Log::error('Alumni Import Error: ' . $e->getMessage());
            $this->errorMessage = 'An unexpected error occurred during import: ' . $e->getMessage();
        } finally {
            $this->importing = false;
            $this->reset('upload');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['upload', 'importing', 'importFinished', 'successMessage', 'errorMessage', 'failures', 'importedCount', 'failedCount', 'skippedCount']);
    }

    public function render()
    {
        return view('livewire.admin.alumni-upload');
    }
}
