<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityEvent extends Model
{
    protected $fillable = [
        'type',
        'description',
        'severity',
        'ip_address',
        'user_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
