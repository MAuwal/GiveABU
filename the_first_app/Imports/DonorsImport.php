<?php

namespace App\Imports;

use App\Models\Donor;
use App\Models\Faculty;
use App\Models\Department;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;

class DonorsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError, WithChunkReading
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
            Log::info("Processing row: " . json_encode($row));
            
            // Check if donor already exists by email or reg_number
            $existingDonor = Donor::where('email', trim($row['email']))
                ->orWhere('reg_number', trim($row['reg_number']))
                ->first();

            if ($existingDonor) {
                $this->skippedCount++;
                Log::info("Skipping duplicate donor: " . $row['email']);
                return null;
            }

            // Clean and validate data
            $entryYear = $this->cleanYear($row['entry_year']);
            $graduationYear = $this->cleanYear($row['graduation_year']);
            
            Log::info("Years after cleaning: entry=" . $entryYear . ", graduation=" . $graduationYear);
            
            // Skip if years are invalid
            if (!$entryYear || !$graduationYear || $graduationYear < $entryYear) {
                $this->skippedCount++;
                Log::warning("Skipping invalid years: entry={$row['entry_year']}->{$entryYear}, graduation={$row['graduation_year']}->{$graduationYear}");
                return null;
            }

            // Find or create Faculty
            $faculty = Faculty::firstOrCreate(
                ['current_name' => trim($row['faculty'])],
                ['current_name' => trim($row['faculty'])]
            );

            Log::info("Faculty: " . $faculty->current_name . " (ID: " . $faculty->id . ")");

            // Find or create Department and associate it with the faculty
            $department = Department::firstOrCreate(
                [
                    'current_name' => trim($row['department']),
                    'faculty_id' => $faculty->id
                ],
                [
                    'current_name' => trim($row['department']),
                    'faculty_id' => $faculty->id
                ]
            );

            Log::info("Department: " . $department->current_name . " (ID: " . $department->id . ")");

            // Normalize gender if provided
            $gender = isset($row['gender']) ? strtolower(trim($row['gender'])) : null;
            if (!in_array($gender, ['male', 'female'])) {
                $gender = null;
            }

            $donor = new Donor([
                'surname'            => trim($row['surname']),
                'name'               => trim($row['name']),
                'other_name'         => trim($row['other_name'] ?? ''),
                'gender'             => $gender,
                'reg_number'         => trim($row['reg_number']),
                'lga'                => trim($row['lga']),
                'nationality'        => trim($row['nationality']),
                'state'              => trim($row['state']),
                'address'            => trim($row['address']),
                'email'              => trim($row['email']),
                'phone'              => (string) $row['phone'], // Convert to string
                'entry_year'         => $entryYear,
                'graduation_year'    => $graduationYear,
                'donor_type'         => 'addressable_alumni', // Default type for imported alumni
                'faculty_id'         => $faculty->id,
                'department_id'      => $department->id,
            ]);

            $this->importedCount++;
            Log::info("Creating donor: " . $row['email'] . " (ID will be: " . $donor->id . ")");
            
            return $donor;

        } catch (\Exception $e) {
            Log::error("Error creating donor: " . $e->getMessage() . " for row: " . json_encode($row));
            $this->skippedCount++;
            return null;
        }
    }

    /**
     * Clean and validate year data
     */
    private function cleanYear($year)
    {
        // Convert to integer first
        $year = (int) $year;
        
        // Log the original year for debugging
        Log::info("Year conversion: original=" . $year);
        
        // Check if this is an Excel date serial number (very large numbers)
        // Excel dates are days since January 1, 1900
        if ($year > 10000) {
            // This is likely an Excel date serial number
            // Convert Excel serial number to actual date
            $excelDate = $year - 25569; // Excel epoch starts from 1900-01-01, Unix from 1970-01-01
            $unixTimestamp = $excelDate * 86400; // Convert days to seconds
            
            // Get the year from the timestamp
            $actualYear = (int) date('Y', $unixTimestamp);
            
            Log::info("Excel date conversion: serial={$year} -> year={$actualYear}");
            
            // Validate the converted year
            if ($actualYear >= 1950 && $actualYear <= 2050) {
                return $actualYear;
            } else {
                Log::warning("Converted year out of range: " . $actualYear);
                return null;
            }
        }
        
        // If it's already a reasonable year, use it directly
        if ($year >= 1950 && $year <= 2050) {
            Log::info("Using year directly: " . $year);
            return $year;
        }
        
        Log::warning("Year out of range: " . $year);
        return null;
    }

    public function rules(): array
    {
        return [
            '*.surname' => ['required', 'string'],
            '*.name' => ['required', 'string'],
            '*.reg_number' => ['required', 'string'],
            '*.lga' => ['required', 'string'],
            '*.nationality' => ['required', 'string'],
            '*.state' => ['required', 'string'],
            '*.address' => ['required', 'string'],
            '*.email' => ['required', 'email'],
            '*.phone' => ['required'], // Remove string validation since we convert it
            '*.entry_year' => ['required'], // Remove integer validation since we handle it manually
            '*.graduation_year' => ['required'], // Remove integer validation since we handle it manually
            '*.faculty' => ['required', 'string'],
            '*.department' => ['required', 'string'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.surname.required' => 'Surname is required for all rows.',
            '*.name.required' => 'Name is required for all rows.',
            '*.reg_number.required' => 'Registration number is required for all rows.',
            '*.lga.required' => 'LGA is required for all rows.',
            '*.nationality.required' => 'Nationality is required for all rows.',
            '*.state.required' => 'State is required for all rows.',
            '*.address.required' => 'Address is required for all rows.',
            '*.email.required' => 'Email is required for all rows.',
            '*.email.email' => 'Email must be a valid email address.',
            '*.phone.required' => 'Phone number is required for all rows.',
            '*.entry_year.required' => 'Entry year is required for all rows.',
            '*.graduation_year.required' => 'Graduation year is required for all rows.',
            '*.faculty.required' => 'Faculty is required for all rows.',
            '*.department.required' => 'Department is required for all rows.',
        ];
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
