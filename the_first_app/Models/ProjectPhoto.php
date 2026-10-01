<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectPhoto extends Model
{
    protected $fillable = [
        'project_id',
        'body_image',
        'title',
        'description',
    ];

    /**
     * Get the project that owns this photo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->body_image ? Storage::disk('public')->url($this->body_image) : null;
    }
}
