<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyVision extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'name',
        'start_year',
        'end_year',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}
