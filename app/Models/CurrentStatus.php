<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrentStatus extends Model
{
  protected $table = 'current_status';
  protected $fillable = [
    'last_heartbeat_at',
    'system_available',
    'thermal_range',
    'thermal_temperature',
    'hdd_a_ok',
    'hdd_a_health',
    'hdd_a_free_p',
    'hdd_b_ok',
    'hdd_b_health',
    'hdd_b_free_p',
    'encryption_ok',
    'service_docker_ok',
    'service_nginx_ok',
    'container_buero_ok',
    'container_medien_ok',
    'container_doku_ok',
  ];

  protected $casts = [
    'last_heartbeat_at' => 'datetime',
    'system_available' => 'boolean',
    'hdd_a_ok' => 'boolean',
    'hdd_a_health' => 'boolean',
    'hdd_b_ok' => 'boolean',
    'hdd_b_health' => 'boolean',
    'encryption_ok' => 'boolean',
    'service_docker_ok' => 'boolean',
    'service_nginx_ok' => 'boolean',
    'container_buero_ok' => 'boolean',
    'container_medien_ok' => 'boolean',
    'container_doku_ok' => 'boolean',
    'hdd_a_free_p' => 'float',
    'hdd_b_free_p' => 'float',
  ];
}
