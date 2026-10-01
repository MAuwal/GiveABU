<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class DonorSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password',
        'donor_id',
        'device_session_id',
        // Google OAuth fields
        'auth_provider',
        'google_id',
        'google_email',
        'google_name',
        'google_picture',
        // Email verification
        'email_verified_at',
        'email_verification_token',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'auth_provider' => 'string',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Automatically hash the password when setting it
     * Only hash if value is not null (for Google OAuth users, password is null)
     */
    public function setPasswordAttribute($value)
    {
        if ($value !== null) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = null;
        }
    }

    /**
     * Check if this session is authenticated via Google
     */
    public function isGoogleAuth(): bool
    {
        return $this->auth_provider === 'google';
    }

    /**
     * Relationship to Donor
     */
    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * Get the device session associated with this donor session.
     */
    public function deviceSession()
    {
        return $this->belongsTo(DeviceSession::class);
    }
}
