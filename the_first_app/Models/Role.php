<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    protected $fillable = ['role_title', 'permission_id'];

    /**
     * The users that belong to the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * The permission that belongs to the role.
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
