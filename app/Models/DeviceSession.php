<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id',
        'session_token',
        'device_fingerprint',
        'user_agent',
        'ip_address',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * Get all donor sessions associated with this device session.
     */
    public function donorSessions()
    {
        return $this->hasMany(DonorSession::class);
    }
} 