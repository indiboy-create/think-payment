<?php

declare(strict_types=1);

use Yansongda\Pay\Pay;

return [
    'alipay' => [
        'default' => [
            'app_id' => 'test-alipay-app-id',
            'app_secret_cert' => 'test-app-secret',
            'notify_url' => 'https://example.com/alipay/notify',
            'mode' => Pay::MODE_NORMAL,
        ],
    ],
    'wechat' => [
        'default' => [
            'mch_id' => 'test-mch-id',
            'mp_app_id' => 'test-mp-app-id',
            'mode' => Pay::MODE_NORMAL,
        ],
    ],
    'logger' => [
        'enable' => true,
        'channel' => 'file',
    ],
    'http' => [
        'timeout' => 3.0,
        'connect_timeout' => 1.0,
    ],
];