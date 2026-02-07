<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentAvailability extends Model
{

  protected $table = 'availability_recently';
  public $timestamps = false;

  protected $fillable = [
    'probed_at',
    'is_available',
    'response_time_ms',
    'response_code',
    'error_kind',
  ];

  protected $casts = [
    'probed_at' => 'datetime',
    'is_available' => 'boolean',
  ];

}
