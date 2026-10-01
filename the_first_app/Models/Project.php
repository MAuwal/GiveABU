<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_title',
        'project_description',
        'icon_image',
        'target',
        'raised',
        'category_id',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($project) {
            if ($project->target > 0 && $project->raised >= $project->target) {
                $project->status = 'closed';
            }
        });
    }

    protected $dates = ['deleted_at'];
    /**
     * Get the photos for this project
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ProjectPhoto::class);
    }

    /**
     * Get the icon image URL
     */
    public function getIconImageUrlAttribute(): ?string
    {
        return $this->icon_image ? Storage::disk('public')->url($this->icon_image) : null;
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function getTotalDonationsAttribute()
    {
        return $this->donations()->sum('amount');
    }

    /**
     * Get the category that owns this project.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class);
    }

    public function details()
    {
        return $this->hasOne(ProjectDetail::class);
    }
}
