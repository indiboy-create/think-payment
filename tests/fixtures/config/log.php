<?php

declare(strict_types=1);

return [
    'default' => 'file',
    'level' => [],
    'channels' => [
        'file' => [
            'type' => 'file',
            'path' => __DIR__.'/../runtime/log',
            'single' => 'payment-test',
            'realtime_write' => true,
        ],
    ],
];