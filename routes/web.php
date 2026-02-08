<?php

use App\Models\CurrentStatus;
use App\Models\RecentAvailability;
use App\Models\StatsAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (Request $request) {

  $stats = StatsAvailability::query()
    ->select([
        'date',
        'availability_p',
        'avg_response_time_ms',
    ])
    ->latest('date')
    ->limit(60)
    ->get();

  $currentStatus = CurrentStatus::query()
    ->latest()
    ->first([
      'status_json',
      'system_ok',
    ]);

  $latestAvailability = RecentAvailability::query()
    ->select([
      'probed_at',
      'is_available',
      'response_time_ms',
      'response_code',
      'error_kind',
    ])
    ->latest('probed_at')
    ->first();

  return Inertia::render('MainPage', [
    'performance' => $stats,
    'internal_check' => $currentStatus?->status_json,
    'internal_ok' => $currentStatus?->system_ok,
    'external_check' => $latestAvailability,
  ]);
})->name('main');
