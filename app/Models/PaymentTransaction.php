<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'donation_id',
        'donor_id',
        'project_id',
        'payment_gateway',
        'category',
        'event_type',
        'payment_reference',
        'gateway_reference',
        'amount',
        'currency',
        'status',
        'gateway_status',
        'channel',
        'fee',
        'message',
        'metadata',
        'response_payload',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
    ];

    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
