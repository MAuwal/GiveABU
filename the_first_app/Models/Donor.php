<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    use HasFactory;

    protected $fillable = [
        'surname',
        'name',
        'other_name',
        'gender',
        'reg_number',
        'lga',
        'nationality',
        'state',
        'address',
        'email',
        'profile_image',
        'phone',
        'entry_year',
        'graduation_year',
        'donor_type',
        'organization_name',
        'ranking',
        'faculty_id',
        'department_id',
        'donor_tier_id',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function tier()
    {
        return $this->belongsTo(DonorTier::class, 'donor_tier_id');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function successfulDonations()
    {
        // Return all donations, regardless of status
        return $this->hasMany(\App\Models\Donation::class);
    }

    public function donorSession()
    {
        return $this->hasOne(DonorSession::class);
    }

    // Accessor for full name
    public function getFullNameAttribute()
    {
        return trim("{$this->surname} {$this->name} {$this->other_name}");
    }

    /**
     * Get the profile image URL
     */
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        return null;
    }
}
