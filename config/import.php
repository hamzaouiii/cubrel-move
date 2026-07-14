<?php

return [

    
    'excluded_fields' => ['record', 'address', 'image'],

    'accepted_extensions' => ['csv', 'json'],

    'max_stored_errors' => 100,

    'max_rows' => 50000,

    'sync_row_threshold' => 200,
    // in KB
    'max_file_size_kb' => 10240,

    'statuses' => [
        'pending' => 'pending',
        'mapping' => 'mapping',
        'queued' => 'queued',
        'processing' => 'processing',
        'completed' => 'completed',
        'failed' => 'failed',
    ],

    'checkbox_true_values' => ['1', 'true', 'yes', 'y', 'on'],

    'checkbox_false_values' => ['0', 'false', 'no', 'n', 'off', ''],
    'timeout' => 3600,
    'tries' => 1

];
