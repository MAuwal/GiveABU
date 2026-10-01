<?php

namespace App\Imports;

use App\Models\Faculty;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class FacultiesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Faculty([
            'current_name' => $row['faculty_name'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.faculty_name' => ['required', 'string', 'unique:faculties,current_name'],
        ];
    }
}
