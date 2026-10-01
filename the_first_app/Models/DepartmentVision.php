<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentVision extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'faculty_version_id',
        'name',
        'start_year',
        'end_year',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function facultyVision()
    {
        return $this->belongsTo(FacultyVision::class, 'faculty_version_id');
    }
}
