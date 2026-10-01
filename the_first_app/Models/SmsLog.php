<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_phone',
        'sender_id',
        'message',
        'status',
        'error_message',
        'cost',
        'response_payload',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
