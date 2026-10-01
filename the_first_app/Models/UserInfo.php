<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;

    protected $table = 'users_info';

    protected $fillable = [
        'user_id',
        'address',
        'phone',
        'profile_photo',
        'state',
        'city',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 