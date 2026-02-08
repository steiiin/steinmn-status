<?php

use App\Services\StatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/heartbeat', function (Request $request, StatusService $ss) {
  $ss->handleHeartbeat($request);

  return response()->json([
    'status' => 'ok',
  ]);

});
