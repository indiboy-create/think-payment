## ThinkPHP-PanGu - 盘古ThinkPHP开源索引!
面向 ThinkPHP 的开源工程索引：组件、框架、服务与发布工具，按能力组织，持续整理。
仓库地址：**<https://github.com/indiboy-create/ThinkPHP-PanGu/>**
在线站点：**<https://indiboy-create.github.io/ThinkPHP-PanGu/>**

# youvision/think-payment

基于 [yansongda/pay](https://github.com/yansongda/pay) 的 ThinkPHP 8 支付扩展包，支持支付宝、微信支付等渠道。

已适配 ThinkPHP 8 的容器、事件、日志与配置体系，可直接在控制器、模型和事件监听中使用。

## 环境要求

- PHP >= 8.1
- ThinkPHP >= 8.0
- 支付宝、微信支付等渠道的商户配置

## 安装

```bash
composer require youvision/think-payment
```

安装完成后，生成 ThinkPHP 服务注册文件：

```bash
php think service:discover
```

也可以手动在 `config/service.php` 中注册：

```php
return [
    \Youvision\ThinkPayment\Service::class,
];
```

## 发布配置

包内已经提供默认配置，未发布时也会自动读取。需要修改时执行：

```bash
php think vendor:publish
```

配置文件会被复制到 `config/payment.php`，各配置项均有详细中文注释。

支持使用 `.env` 配置敏感信息，例如：

```dotenv
PAY_ALIPAY_APP_ID=your-app-id
PAY_ALIPAY_APP_SECRET_CERT=/path/to/app_private_key.pem
PAY_ALIPAY_PUBLIC_CERT_PATH=/path/to/alipay_public_key.pem
PAY_ALIPAY_NOTIFY_URL=https://example.com/pay/alipay/notify
PAY_WECHAT_MCH_ID=your-mch-id
PAY_WECHAT_MP_APP_ID=your-mp-app-id
PAY_WECHAT_MCH_SECRET_KEY=your-32-byte-api-v3-key
PAY_WECHAT_MCH_SECRET_CERT=/path/to/apiclient_key.pem
PAY_WECHAT_NOTIFY_URL=https://example.com/pay/wechat/notify
```

## 使用

### 支付宝电脑网站支付

```php
use Yansongda\Pay\Pay;

$response = Pay::alipay()->web([
    'out_trade_no' => time().'',
    'total_amount' => '0.01',
    'subject' => '测试商品',
]);

// yansongda/pay 返回 PSR-7 Response，需要转换为 ThinkPHP 响应
return response($response->getBody()->getContents());
```

### 支付宝手机网站支付

```php
$response = Pay::alipay()->h5([
    'out_trade_no' => time().'',
    'total_amount' => '0.01',
    'subject' => '测试商品',
]);

return response($response->getBody()->getContents());
```

### 微信 Native 扫码支付

```php
$result = Pay::wechat()->scan([
    'out_trade_no' => time().'',
    'description' => '测试商品',
    'amount' => [
        'total' => 1,
        'currency' => 'CNY',
    ],
]);

return json($result->all());
```

### 微信小程序支付

```php
$result = Pay::wechat()->mini([
    'out_trade_no' => time().'',
    'description' => '测试商品',
    'amount' => [
        'total' => 1,
        'currency' => 'CNY',
    ],
    'payer' => [
        'openid' => 'user-openid',
    ],
]);

return json($result->all());
```

### 支付回调

```php
use Yansongda\Pay\Pay;

public function notify()
{
    try {
        // 支付宝
        $result = Pay::alipay()->callback();

        // 微信
        // $result = Pay::wechat()->callback();

        $outTradeNo = $result->get('out_trade_no');

        // 处理订单业务，注意做好幂等
    } catch (\Throwable $e) {
        // 验签失败或参数异常，记录日志
    }

    // 返回渠道要求的成功响应
    $response = Pay::alipay()->success();

    return response($response->getBody()->getContents());
}
```

如果项目运行在 Swoole、Workerman 等常驻内存模式下，SDK 无法读取超全局变量，
需要显式传入当前请求：

```php
$result = Pay::alipay()->callback(payment_request());
$result = Pay::wechat()->callback(payment_request());
```

`payment_request()` 会把当前 ThinkPHP 请求转换为 PSR-7 请求。

### 查询、退款、关闭订单

```php
$result = Pay::alipay()->query([
    'out_trade_no' => '20240101000001',
]);

$result = Pay::alipay()->refund([
    'out_trade_no' => '20240101000001',
    'refund_amount' => '0.01',
]);

$result = Pay::wechat()->close([
    'out_trade_no' => '20240101000001',
]);
```

其它渠道与接口用法与 [yansongda/pay 官方文档](https://pay.yansongda.cn) 保持一致。

### 助手函数

```php
$alipay = payment('alipay');
$wechat = payment('wechat');
```

需要切换商户时请使用下文的多租户方式，或在 `config/payment.php` 中调整配置。

## 多租户

配置文件中可以在 `default` 之外增加多组商户配置：

```php
'alipay' => [
    'default' => [],
    'merchant_2' => [],
],
```

调用时通过 `_config` 指定租户：

```php
Pay::alipay()->web([
    '_config' => 'merchant_2',
    'out_trade_no' => time().'',
    'total_amount' => '0.01',
    'subject' => '测试商品',
]);
```

## 事件监听

SDK 事件已经桥接到 ThinkPHP 事件系统，可在 `app/event.php` 中监听：

```php
return [
    'listen' => [
        \Yansongda\Pay\Event\PayStart::class => [
            \app\listener\PayStart::class,
        ],
        \Yansongda\Pay\Event\PayEnd::class => [
            \app\listener\PayEnd::class,
        ],
        \Yansongda\Pay\Event\CallbackReceived::class => [
            \app\listener\PayCallbackReceived::class,
        ],
    ],
];
```

监听器示例：

```php
namespace app\listener;

use Yansongda\Pay\Event\PayStart;

class PayStart
{
    public function handle(PayStart $event): void
    {
        // $event->plugins、$event->params
    }
}
```

## 日志

SDK 日志统一写入 ThinkPHP 日志体系：

```php
'logger' => [
    'enable' => true,
    'channel' => 'payment',
],
```

`channel` 对应 `config/log.php` 中的通道名称，留空时使用默认通道。日志落盘位置、级别、切分方式均由 ThinkPHP 日志配置决定。

## 运行测试

```bash
php vendor/bin/phpunit
```