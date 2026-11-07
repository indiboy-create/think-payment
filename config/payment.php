<?php

declare(strict_types=1);

use Yansongda\Pay\Pay;

return [
    /*
    |--------------------------------------------------------------------------
    | 支付宝配置
    |--------------------------------------------------------------------------
    | 支持多租户：在 default 之外再增加一组配置（如 merchant_2），
    | 请求时通过 _config 参数指定租户，例如：['_config' => 'merchant_2']。
    */
    'alipay' => [
        'default' => [
            // 支付宝开放平台分配的应用 AppID
            'app_id' => env('PAY_ALIPAY_APP_ID', ''),

            // 应用私钥。可直接填写私钥字符串，也可填写私钥文件路径
            'app_secret_cert' => env('PAY_ALIPAY_APP_SECRET_CERT', ''),

            // 应用公钥证书路径（证书模式必填）
            'app_public_cert_path' => env('PAY_ALIPAY_APP_PUBLIC_CERT_PATH', ''),

            // 支付宝公钥证书路径；使用公钥模式时填写支付宝公钥内容
            'alipay_public_cert_path' => env('PAY_ALIPAY_PUBLIC_CERT_PATH', ''),

            // 支付宝根证书路径（证书模式必填）
            'alipay_root_cert_path' => env('PAY_ALIPAY_ROOT_CERT_PATH', ''),

            // 支付完成后同步跳转地址（浏览器跳转）
            'return_url' => env('PAY_ALIPAY_RETURN_URL', ''),

            // 支付完成后异步通知地址（支付宝服务器回调，必须是公网可访问的 URL）
            'notify_url' => env('PAY_ALIPAY_NOTIFY_URL', ''),

            // 运行模式：0 正常模式、1 沙箱模式、2 服务商模式
            'mode' => Pay::MODE_NORMAL,

            // 服务商模式下的服务商 PID，仅在 mode 为 MODE_SERVICE 时使用
            'service_provider_id' => env('PAY_ALIPAY_SERVICE_PROVIDER_ID', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 微信支付配置
    |--------------------------------------------------------------------------
    | 支持多租户：在 default 之外再增加一组配置，请求时通过 _config 指定租户。
    */
    'wechat' => [
        'default' => [
            // 微信支付商户号
            'mch_id' => env('PAY_WECHAT_MCH_ID', ''),

            // 公众号 AppID（公众号支付、JSAPI 支付使用）
            'mp_app_id' => env('PAY_WECHAT_MP_APP_ID', ''),

            // 小程序 AppID（小程序支付使用）
            'mini_app_id' => env('PAY_WECHAT_MINI_APP_ID', ''),

            // APP AppID（APP 支付使用）
            'app_id' => env('PAY_WECHAT_APP_ID', ''),

            // APIv3 密钥，固定 32 字节，用于回调报文解密
            'mch_secret_key' => env('PAY_WECHAT_MCH_SECRET_KEY', ''),

            // 商户 API 私钥，可直接填写私钥内容或私钥文件路径
            'mch_secret_cert' => env('PAY_WECHAT_MCH_SECRET_CERT', ''),

            // 商户 API 证书公钥路径
            'mch_public_cert_path' => env('PAY_WECHAT_MCH_PUBLIC_CERT_PATH', ''),

            // 公众号支付所使用的公钥证书路径
            'mp_public_cert_path' => env('PAY_WECHAT_MP_PUBLIC_CERT_PATH', ''),

            // 公众号支付所使用的私钥证书路径
            'mp_private_cert_path' => env('PAY_WECHAT_MP_PRIVATE_CERT_PATH', ''),

            // 支付完成后异步通知地址，必须是公网可访问的 URL
            'notify_url' => env('PAY_WECHAT_NOTIFY_URL', ''),

            // 微信支付平台证书，格式为 [证书序列号 => 证书内容或证书路径]
            // 留空时 SDK 会在需要时自动下载并缓存
            'wechat_public_cert_path' => [],

            // 运行模式：0 正常模式、1 沙箱模式、2 服务商模式
            'mode' => Pay::MODE_NORMAL,

            // 服务商模式下的服务商商户号，仅在 mode 为 MODE_SERVICE 时使用
            'service_provider_id' => env('PAY_WECHAT_SERVICE_PROVIDER_ID', ''),

            // APIv2 密钥，仅在调用 APIv2 接口时使用
            'mch_secret_key_v2' => env('PAY_WECHAT_MCH_SECRET_KEY_V2', ''),

            // 小程序虚拟支付密钥，仅在微信小程序虚拟支付场景使用
            'mini_app_key_virtual_pay' => env('PAY_WECHAT_MINI_APP_KEY_VIRTUAL_PAY', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 日志配置
    |--------------------------------------------------------------------------
    | 日志写入 ThinkPHP 日志体系，具体落盘位置由 config/log.php 决定。
    */
    'logger' => [
        // 是否记录 SDK 请求与响应日志
        'enable' => (bool) env('PAY_LOGGER_ENABLE', false),

        // ThinkPHP 日志通道名称，留空使用默认通道（对应 config/log.php 中的 channels）
        'channel' => env('PAY_LOGGER_CHANNEL', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP 客户端配置
    |--------------------------------------------------------------------------
    | 参数原样传递给 Guzzle，可按需增加 proxy、verify 等选项。
    */
    'http' => [
        // 请求超时时间（秒）
        'timeout' => (float) env('PAY_HTTP_TIMEOUT', 5.0),

        // 建立连接超时时间（秒）
        'connect_timeout' => (float) env('PAY_HTTP_CONNECT_TIMEOUT', 3.0),

        // 默认请求头
        'headers' => [
            'User-Agent' => 'youvision/think-payment',
        ],
    ],
];