<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'donor_id',
        'project_id',
        'amount',
        'type',
        'frequency',
        'endowment',
        'status',
        'payment_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'endowment' => 'string', // 'yes' or 'no'
    ];

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
