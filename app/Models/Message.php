<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'message',
        'is_read',
    ];

    /**
     * Get the donor who sent the message
     */
    public function sender()
    {
        return $this->belongsTo(Donor::class, 'sender_id');
    }

    /**
     * Get the donor who received the message
     */
    public function receiver()
    {
        return $this->belongsTo(Donor::class, 'receiver_id');
    }

    /**
     * Scope for conversation between two donors
     */
    public function scopeConversation($query, $donor1, $donor2)
    {
        return $query->where(function($q) use ($donor1, $donor2) {
            $q->where('sender_id', $donor1)->where('receiver_id', $donor2);
        })->orWhere(function($q) use ($donor1, $donor2) {
            $q->where('sender_id', $donor2)->where('receiver_id', $donor1);
        });
    }
}