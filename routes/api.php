<?php

use App\Models\CurrentStatus;
use App\Services\StatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/heartbeat', function (Request $request, StatusService $ss) {

  # validate bearer
  $providedToken = $request->bearerToken();
  $expectedToken = (string) config('heartbeat.token');
  if ($expectedToken === '' || !is_string($providedToken)) {
    abort(401, 'Unauthorized');
  }
  if (!hash_equals($expectedToken, $providedToken)) {
    abort(401, 'Unauthorized');
  }

  # validate hostId
  $hostId = $request->header('X-Host-Id');
  if (!is_string($hostId) || $hostId === '') {
    abort(400, 'Missing X-Host-Id');
  }

  # validate JSON
  $request->validate([

    'thermal' => 'required|array',
    'thermal.ts' => 'required|date',
    'thermal.temp' => 'required|integer|min:0|max:256',
    'thermal.range' => 'required|in:HIGH,MEDIUM,LOW',

    'hdd-a' => 'required|array',
    'hdd-a.ts' => 'required|date',
    'hdd-a.ok' => 'required|in:true,false',
    'hdd-a.health' => 'required|in:true,false',
    'hdd-a.error' => 'nullable|in:smartctl_error,smart_failed,attr_pending,attr_offline_uncorrectable,attr_reallocated,selftest_errors',
    'hdd-a.free_p' => 'required|numeric|min:0|max:1',

    'hdd-b' => 'required|array',
    'hdd-b.ts' => 'required|date',
    'hdd-b.ok' => 'required|in:true,false',
    'hdd-b.health' => 'required|in:true,false',
    'hdd-b.error' => 'nullable|in:smartctl_error,smart_failed,attr_pending,attr_offline_uncorrectable,attr_reallocated,selftest_errors',
    'hdd-b.free_p' => 'required|numeric|min:0|max:1',

    'encryption' => 'required|array',
    'encryption.ts' => 'required|date',
    'encryption.ok' => 'required|in:true,false',

    'service-nginx' => 'required|array',
    'service-nginx.ts' => 'required|date',
    'service-nginx.ok' => 'required|in:true,false',
    'service-nginx.error' => 'nullable|in:crashed,deactivated',

    'service-docker' => 'required|array',
    'service-docker.ts' => 'required|date',
    'service-docker.ok' => 'required|in:true,false',
    'service-docker.error' => 'nullable|in:crashed,deactivated',

    'container-buero' => 'required|array',
    'container-buero.ts' => 'required|date',
    'container-buero.ok' => 'required|in:true,false',
    'container-buero.nextcloud' => 'required|in:true,false',
    'container-buero.nc_db' => 'required|in:true,false',
    'container-buero.nc_redis' => 'required|in:true,false',
    'container-buero.nc_base' => 'required|in:true,false',

    'container-dokumente' => 'required|array',
    'container-dokumente.ts' => 'required|date',
    'container-dokumente.ok' => 'required|in:true,false',
    'container-dokumente.paperless' => 'required|in:true,false',
    'container-dokumente.pl_db' => 'required|in:true,false',
    'container-dokumente.pl_redis' => 'required|in:true,false',
    'container-dokumente.pl_base' => 'required|in:true,false',

    'container-medien' => 'required|array',
    'container-medien.ts' => 'required|date',
    'container-medien.ok' => 'required|in:true,false',
    'container-medien.jellyfin' => 'required|in:true,false',
    'container-medien.jf_base' => 'required|in:true,false',

  ]);

  // update current state
  $systemOk = toBool($request['hdd-a']['ok'])
    && toBool($request['hdd-b']['ok'])
    && toBool($request['encryption']['ok'])
    && toBool($request['service-nginx']['ok'])
    && toBool($request['service-docker']['ok'])
    && toBool($request['container-buero']['ok'])
    && toBool($request['container-dokumente']['ok'])
    && toBool($request['container-medien']['ok']);

  $tz = config('app.timezone');
  $payload = [
    'last_heartbeat_at'   => now($tz),
    'system_ok'           => $systemOk ? 1 : 0,
    'status_json'         => normalizeBoolValues($request->all()),
    'issues_json'         => [],
  ];

  $currentStatus = CurrentStatus::first();
  if ($currentStatus) {
    $currentStatus->update($payload);
  } else {
    CurrentStatus::create($payload);
  }

  $ss->run();

  return response()->json([
    'status' => 'ok',
  ]);

});

function toBool(mixed $value): bool {
  if (is_bool($value)) {
    return $value;
  }

  if (is_string($value)) {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
  }

  return (bool) $value;
}

function normalizeBoolValues(mixed $value): mixed {
  if (is_array($value)) {
    $normalized = [];
    foreach ($value as $key => $item) {
      $normalized[$key] = normalizeBoolValues($item);
    }
    return $normalized;
  }

  if (is_string($value) && ($value === 'true' || $value === 'false')) {
    return $value === 'true';
  }

  return $value;
}
