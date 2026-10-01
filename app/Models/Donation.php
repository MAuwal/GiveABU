<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'receipt_phone',
        'donor_id',
        'project_id',
        'amount',
        'type',
        'frequency',
        'endowment',
        'status',
        'payment_reference',
        'paid_at',
        'verified_at',
    ];

    protected $hidden = ['receipt_phone'];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'endowment' => 'string', // 'yes' or 'no'
    ];

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
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
