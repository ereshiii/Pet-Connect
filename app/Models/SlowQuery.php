<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlowQuery extends Model
{
    protected $fillable = [
        'query',
        'bindings',
        'time_ms',
        'file',
        'line',
    ];

    protected $casts = [
        'bindings' => 'array',
    ];
}
