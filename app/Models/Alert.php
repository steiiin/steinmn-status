<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $table = 'alerts';

    public $timestamps = false;

    protected $fillable = [
        'kind',
        'issues',
        'subject',
        'body',
        'alerted_at',
    ];

    protected $casts = [
        'issues' => 'array',
        'alerted_at' => 'datetime',
    ];
}
