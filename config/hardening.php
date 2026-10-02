<?php

return [
    'receipts_per_minute' => (int) env('PAYMENT_RECEIPTS_PER_MINUTE', 60),
    'drain_file' => storage_path('framework/node-draining'),
    'readiness_disks' => array_values(array_filter(array_map('trim', explode(',', env('READINESS_STORAGE_DISKS', ''))))),
    'payment_recovery_enabled' => (bool) env('PAYMENT_RECOVERY_ENABLED', false),
];
