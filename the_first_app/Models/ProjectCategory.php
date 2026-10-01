<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'department_id',
        'faculty_id',
    ];

    /**
     * Get the department that owns this category.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the faculty that owns this category.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the projects for this category.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'category_id');
    }
}
