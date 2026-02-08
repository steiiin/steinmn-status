<?php

return [
  'url' => env('MONITOR_URL'),
  'timeout' => (int) env('MONITOR_TIMEOUT', 5),
  'slow_server_ms' => (int) env('SLOW_SERVER_MS', 1000),
  'email_to' => env('MONITOR_EMAIL_TO'),
  'email_from' => env('MONITOR_EMAIL_FROM'),
];
