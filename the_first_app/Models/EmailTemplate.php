<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body_html',
        'body_text',
        'is_active',
        'variables',
        'donor_tier_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variables' => 'array',
    ];

    public function logs()
    {
        return $this->hasMany(EmailLog::class, 'template_id');
    }

    public function tier()
    {
        return $this->belongsTo(DonorTier::class, 'donor_tier_id');
    }
}
