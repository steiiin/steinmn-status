<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatsAvailability extends Model
{

  protected $table = 'availability_stats';
  public $timestamps = false;

  protected $fillable = [
    'date',
    'avg_response_time_ms',
    'availability_p',
    'samples_total',
    'samples_up',
    'coverage_p'
  ];

}
