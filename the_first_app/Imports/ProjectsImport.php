<?php

namespace App\Imports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;

class ProjectsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError, WithBatchInserts, WithChunkReading
{
    use SkipsFailures, SkipsErrors;

    public $importedCount = 0;
    public $skippedCount = 0;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        try {
            // Log the incoming row data for debugging
            Log::info("Processing project row: " . json_encode($row));
            
            // Check if project already exists by title
            $existingProject = Project::where('project_title', trim($row['project_title']))->first();

            if ($existingProject) {
                $this->skippedCount++;
                Log::info("Skipping duplicate project: " . $row['project_title']);
                return null;
            }

            $project = new Project([
                'project_title' => trim($row['project_title']),
                'project_description' => trim($row['project_description']),
                'icon_image' => null, // Will be handled separately if needed
            ]);

            $this->importedCount++;
            Log::info("Creating project: " . $row['project_title']);
            
            return $project;

        } catch (\Exception $e) {
            Log::error("Error creating project: " . $e->getMessage() . " for row: " . json_encode($row));
            $this->skippedCount++;
            return null;
        }
    }

    public function rules(): array
    {
        return [
            '*.project_title' => ['required', 'string', 'max:255'],
            '*.project_description' => ['required', 'string'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.project_title.required' => 'Project title is required for all rows.',
            '*.project_title.string' => 'Project title must be a string.',
            '*.project_title.max' => 'Project title must not exceed 255 characters.',
            '*.project_description.required' => 'Project description is required for all rows.',
            '*.project_description.string' => 'Project description must be a string.',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }
} 