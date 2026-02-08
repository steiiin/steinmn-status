<?php

namespace App\Services;

use App\Models\RecentAvailability;
use App\Models\CurrentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class StatusService
{
  public function handleHeartbeat(Request $request): void
  {
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
    $systemOk = $this->toBool($request['hdd-a']['ok'])
      && $this->toBool($request['hdd-b']['ok'])
      && $this->toBool($request['encryption']['ok'])
      && $this->toBool($request['service-nginx']['ok'])
      && $this->toBool($request['service-docker']['ok'])
      && $this->toBool($request['container-buero']['ok'])
      && $this->toBool($request['container-dokumente']['ok'])
      && $this->toBool($request['container-medien']['ok']);

    $tz = config('app.timezone');
    $payload = [
      'last_heartbeat_at'   => now($tz),
      'system_ok'           => $systemOk ? 1 : 0,
      'status_json'         => $this->normalizeBoolValues($request->all()),
      'issues_json'         => [],
    ];

    $currentStatus = CurrentStatus::first();
    if ($currentStatus) {
      $currentStatus->update($payload);
    } else {
      CurrentStatus::create($payload);
    }

    $this->run();
  }

  public function run(): void
  {

    $monitorUrl = config('monitor.url');

    $headCheck = $this->headCheck($monitorUrl);
    $this->notifyOnHeadCheck($monitorUrl, $headCheck);

    $tz = config('app.timezone');
    RecentAvailability::insert([
      'probed_at' => now($tz),
      'is_available' => $headCheck['ok'] ? 1 : 0,
      'response_time_ms' => $headCheck['time'],
      'response_code' => $headCheck['status'],
      'error_kind' => $headCheck['error'],
    ]);

    $tz = config('app.timezone');
    $day = CarbonImmutable::now($tz)->startOfDay();

    $agg = DB::table('availability_recently')
      ->where('probed_at', '>=', $day)
      ->where('probed_at', '<', $day->addDay())
      ->selectRaw('COUNT(*) as samples_total')
      ->selectRaw('SUM(is_available) as samples_up')
      ->selectRaw('AVG(CASE WHEN is_available = 1 THEN response_time_ms END) as avg_up_ms')
      ->first();

    $samplesTotal = (int) $agg->samples_total;
    $samplesUp    = (int) $agg->samples_up;
    $avgUpMs      = $agg->avg_up_ms !== null ? (float) $agg->avg_up_ms : null;

    $intervalSeconds = max(1, (int) config('monitor.interval_seconds', 60));
    $secondsSoFar = CarbonImmutable::now($tz)->diffInSeconds($day);
    $expectedSoFar = max(1, intdiv($secondsSoFar, $intervalSeconds));

    $availabilityP = $samplesTotal > 0 ? ($samplesUp / $samplesTotal) : 0.0;
    $coverageP     = min(1, ($samplesTotal / $expectedSoFar));

    DB::table('availability_stats')->upsert(
      [[
        'date' => $day->toDateString(),
        'avg_response_time_ms' => $avgUpMs,
        'availability_p' => $availabilityP,
        'samples_total' => $samplesTotal,
        'samples_up' => $samplesUp,
        'coverage_p' => $coverageP,
      ]],
      ['date'], // unique key
      ['avg_response_time_ms', 'availability_p', 'samples_total', 'samples_up', 'coverage_p']
    );

    $this->notifyOnSystemStatus();
  }

  private function headCheck(string $url): array
  {
    try {

      $start = microtime(true);

      $response = Http::timeout((int) config('monitor.timeout', 3))
        ->head($url);

      $durationMs = (microtime(true) - $start) * 1000;

      if ($response->successful()) {
        return [
          'ok'     => true,
          'status' => $response->status(),
          'time'   => $durationMs,
          'error'  => null,
        ];
      }

      // HTTP-level errors
      if ($response->clientError()) {
        return [
          'ok'     => false,
          'status' => $response->status(),
          'time'   => $durationMs,
          'error'  => 'client_error',
        ];
      }

      if ($response->serverError()) {
        return [
          'ok'     => false,
          'status' => $response->status(),
          'time'   => $durationMs,
          'error'  => 'server_error',
        ];
      }

      return [
        'ok'     => false,
        'status' => $response->status(),
        'time'   => $durationMs,
        'error'  => 'unexpected_status',
      ];
    } catch (ConnectionException $e) {

      return [
        'ok' => false,
        'status' => null,
        'time' => null,
        'error' => $this->classifyTransportFailure($e),
      ];
    } catch (\Throwable $e) {

      return [
        'ok'     => false,
        'status' => null,
        'time'   => null,
        'error'  => 'internal_error',
      ];
    }
  }

  private function notifyOnHeadCheck(string $url, array $headCheck): void
  {
    if (!$headCheck['ok']) {
      $bodyLines = [
        'HEAD check failed.',
        "URL: {$url}",
        'Status: ' . ($headCheck['status'] ?? 'unknown'),
        'Error: ' . ($headCheck['error'] ?? 'unknown'),
        'Response time (ms): ' . ($headCheck['time'] !== null ? number_format($headCheck['time'], 2) : 'unknown'),
      ];
      $this->sendAlertEmail('Status monitor HEAD failure', implode("\n", $bodyLines));
      return;
    }

    $slowThreshold = (int) config('monitor.slow_server_ms', 1000);
    if ($headCheck['time'] !== null && $headCheck['time'] > $slowThreshold) {
      $bodyLines = [
        'HEAD check slow response.',
        "URL: {$url}",
        'Status: ' . ($headCheck['status'] ?? 'unknown'),
        'Response time (ms): ' . number_format($headCheck['time'], 2),
        "Slow threshold (ms): {$slowThreshold}",
      ];
      $this->sendAlertEmail('Status monitor slow response', implode("\n", $bodyLines));
    }
  }

  private function notifyOnSystemStatus(): void
  {
    $currentStatus = CurrentStatus::query()->first();
    if (!$currentStatus || $currentStatus->system_ok) {
      return;
    }

    $badParts = $this->collectBadParts($currentStatus->status_json ?? []);
    $bodyLines = [
      'System check reported issues.',
      'Last heartbeat: ' . ($currentStatus->last_heartbeat_at?->toDateTimeString() ?? 'unknown'),
    ];

    if ($badParts !== []) {
      $bodyLines[] = 'Bad parts:';
      foreach ($badParts as $part) {
        $bodyLines[] = "- {$part}";
      }
    }

    $this->sendAlertEmail('Status monitor system check failed', implode("\n", $bodyLines));
  }

  private function collectBadParts(array $statusJson): array
  {
    $badParts = [];

    foreach ($statusJson as $section => $details) {
      if (!is_array($details)) {
        continue;
      }

      foreach ($details as $key => $value) {
        if ($this->isFalseFlag($value)) {
          $badParts[] = "{$section}.{$key}";
        }

        if ($key === 'error' && is_string($value) && $value !== '') {
          $badParts[] = "{$section}.error: {$value}";
        }
      }
    }

    return $badParts;
  }

  private function isFalseFlag(mixed $value): bool
  {
    if (is_bool($value)) {
      return $value === false;
    }

    if (is_string($value)) {
      return strtolower($value) === 'false';
    }

    return false;
  }

  private function sendAlertEmail(string $subject, string $body): void
  {
    $recipients = $this->parseRecipients(config('monitor.email_to'));
    if ($recipients === []) {
      return;
    }

    $from = config('monitor.email_from');

    Mail::raw($body, function ($message) use ($recipients, $from, $subject) {
      $message->to($recipients)->subject($subject);

      if (is_string($from) && $from !== '') {
        $message->from($from);
      }
    });
  }

  private function parseRecipients(?string $raw): array
  {
    if (!is_string($raw) || $raw === '') {
      return [];
    }

    $parts = preg_split('/[;,]/', $raw);
    if ($parts === false) {
      return [];
    }

    return array_values(array_filter(array_map('trim', $parts), fn ($value) => $value !== ''));
  }

  function classifyTransportFailure(\Throwable $e): string
  {
    // Laravel wrapper often contains the real cause
    $prev = $e->getPrevious();

    $msg = strtolower($e->getMessage() . ' ' . ($prev?->getMessage() ?? ''));

    // Timeouts (connect or overall)
    if (str_contains($msg, 'timed out') || str_contains($msg, 'timeout')) {
      return 'connection_timeout';
    }

    // DNS / name resolution
    if (
      str_contains($msg, 'could not resolve') ||
      str_contains($msg, 'name or service not known') ||
      str_contains($msg, 'getaddrinfo') ||
      str_contains($msg, 'dns')
    ) {
      return 'dns_error';
    }

    // TLS / SSL
    if (
      str_contains($msg, 'ssl') ||
      str_contains($msg, 'tls') ||
      str_contains($msg, 'certificate') ||
      str_contains($msg, 'handshake') ||
      str_contains($msg, 'ciphers')
    ) {
      return 'ssl_error';
    }

    // Connection refused / unreachable
    if (
      str_contains($msg, 'connection refused') ||
      str_contains($msg, 'no route to host') ||
      str_contains($msg, 'network is unreachable') ||
      str_contains($msg, 'connection reset')
    ) {
      return 'connection_error';
    }

    return 'transport_error';
  }

  private function toBool(mixed $value): bool
  {
    if (is_bool($value)) {
      return $value;
    }

    if (is_string($value)) {
      return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    return (bool) $value;
  }

  private function normalizeBoolValues(mixed $value): mixed
  {
    if (is_array($value)) {
      $normalized = [];
      foreach ($value as $key => $item) {
        $normalized[$key] = $this->normalizeBoolValues($item);
      }
      return $normalized;
    }

    if (is_string($value) && ($value === 'true' || $value === 'false')) {
      return $value === 'true';
    }

    return $value;
  }
}
