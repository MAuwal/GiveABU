<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonorTier extends Model
{
    protected $fillable = [
        'name',
        'description',
        'min_amount',
        'max_amount',
        'color',
        'icon',
        'benefits',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function donors()
    {
        return $this->hasMany(Donor::class, 'donor_tier_id');
    }
}
