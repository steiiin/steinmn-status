<?php

use App\Services\StatusService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function (StatusService $statusService): void {
    $statusService->run();
})->everyThreeMinutes();

Schedule::call(function (): void {
    $now = Carbon::now();

    DB::table('availability_recently')
        ->where('probed_at', '<', $now->copy()->subWeek())
        ->delete();

    DB::table('availability_stats')
        ->where('date', '<', $now->copy()->subMonths(3)->toDateString())
        ->delete();

    DB::table('alerts')
        ->where('alerted_at', '<', $now->copy()->subMonths(6))
        ->delete();
})->weekly();
