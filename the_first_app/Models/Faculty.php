<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'current_name',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'integer',
        'ended_at' => 'integer',
    ];

    public function departments(){
        return $this->hasMany(Department::class);
    }

    public function visions()
    {
        return $this->hasMany(FacultyVision::class);
    }
}
