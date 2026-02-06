<?php

use App\Models\CurrentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/heartbeat', function (Request $request) {

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
  $systemAvailable = $request['hdd-a']['ok']
    && $request['hdd-b']['ok']
    && $request['encryption']['ok']
    && $request['service-nginx']['ok']
    && $request['service-docker']['ok']
    && $request['container-buero']['ok']
    && $request['container-dokumente']['ok']
    && $request['container-medien']['ok'];

  $current = CurrentStatus::firstOrCreate([], [
    'last_heartbeat_at'   => now(),
    'system_available'    => false,
  ]);
  $current->update([
    'system_available'    => $systemAvailable ? 1 : 0,
    'thermal_range'       => $request['thermal']['range'],
    'thermal_temperature' => $request['thermal']['temp'],
    'hdd_a_ok'            => $request['hdd-a']['ok'] =="true"  ? 1 : 0,
    'hdd_a_health'        => $request['hdd-a']['health'] =="true"  ? 1 : 0,
    'hdd_a_free_p'        => $request['hdd-a']['free_p'],
    'hdd_b_ok'            => $request['hdd-a']['ok'] =="true"  ? 1 : 0,
    'hdd_b_health'        => $request['hdd-a']['health'] =="true"  ? 1 : 0,
    'hdd_b_free_p'        => $request['hdd-a']['free_p'],
    'encryption_ok'       => $request['encryption']['ok'] =="true"  ? 1 : 0,
    'service_docker_ok'   => $request['service-docker']['ok'] =="true"  ? 1 : 0,
    'service_nginx_ok'    => $request['service-nginx']['ok'] =="true"  ? 1 : 0,
    'container_buero_ok'  => $request['container-buero']['ok'] =="true"  ? 1 : 0,
    'container_medien_ok' => $request['container-medien']['ok'] =="true"  ? 1 : 0,
    'container_doku_ok'   => $request['container-dokumente']['ok'] =="true"  ? 1 : 0,
  ]);



  return response()->json([
    'status' => 'ok',
    'message' => $providedToken,
  ]);
});
