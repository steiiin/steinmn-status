<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrentStatus extends Model
{
  protected $table = 'current_status';
  public $timestamps = false;

  protected $fillable = [
    'last_heartbeat_at',
    'system_ok',
    'status_json',
    'issues_json',
  ];

  protected $casts = [
    'last_heartbeat_at' => 'datetime',
    'system_ok' => 'boolean',
    'status_json' => 'array',
    'issues_json' => 'array',
  ];
}
