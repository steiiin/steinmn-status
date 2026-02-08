<?php

namespace App\Services;

use App\Models\RecentAvailability;
use App\Models\CurrentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class StatusService
{
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
}
