<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'background',
        'challenges',
        'proposed_interventions',
        'expected_outcomes',
        'beneficiaries',
        'budget_estimates',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
