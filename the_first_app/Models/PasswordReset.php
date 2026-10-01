<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PasswordReset extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_session_id',
        'token',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    public function donorSession()
    {
        return $this->belongsTo(DonorSession::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function markAsUsed(): void
    {
        $this->forceFill(['used' => true])->save();
    }
}
