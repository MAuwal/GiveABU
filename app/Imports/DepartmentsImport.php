<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Faculty;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DepartmentsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $faculty = Faculty::where('current_name', $row['faculty_name'])->first();

        // If faculty doesn't exist, you might want to skip or handle the error
        if (!$faculty) {
            return null;
        }

        return new Department([
            'current_name' => $row['department_name'],
            'faculty_id' => $faculty->id,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.department_name' => ['required', 'string'],
            '*.faculty_name' => ['required', 'string', 'exists:faculties,current_name'],
        ];
    }
}
