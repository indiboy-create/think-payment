<?php

declare(strict_types=1);

namespace Youvision\ThinkPayment\Tests;

use PHPUnit\Framework\TestCase;
use think\App;
use Yansongda\Artful\Contract\ConfigInterface;
use Yansongda\Artful\Logger as SdkLogger;
use Yansongda\Pay\Event\PayStart;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Wechat;
use Youvision\ThinkPayment\Service;

class ServiceTest extends TestCase
{
    protected App $app;

    protected array $serverBackup = [];

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;

        Pay::clear();

        $this->app = new App(__DIR__.'/fixtures/');
        $this->app->config->load(__DIR__.'/fixtures/config/log.php', 'log');
        $this->app->config->load(__DIR__.'/fixtures/config/payment.php', 'payment');

        (new Service($this->app))->boot();
    }

    protected function tearDown(): void
    {
        Pay::clear();

        $file = __DIR__.'/fixtures/runtime/log/payment-test.log';

        if (is_file($file)) {
            unlink($file);
        }

        $_SERVER = $this->serverBackup;
        $_POST = [];
        $_GET = [];
    }

    public function testAlipayProviderIsRegistered(): void
    {
        $this->assertInstanceOf(Alipay::class, Pay::alipay());
    }

    public function testWechatProviderIsRegistered(): void
    {
        $this->assertInstanceOf(Wechat::class, Pay::wechat());
    }

    public function testProjectConfigIsApplied(): void
    {
        $config = Pay::get(ConfigInterface::class);

        $this->assertSame('test-alipay-app-id', $config->get('alipay.default.app_id'));
        $this->assertSame('test-mch-id', $config->get('wechat.default.mch_id'));
        $this->assertSame(3.0, $config->get('http.timeout'));
    }

    public function testSdkEventIsBridgedToThinkPHP(): void
    {
        $received = null;

        $this->app->event->listen(PayStart::class, function (PayStart $event) use (&$received): void {
            $received = $event->params;
        });

        \Yansongda\Pay\Event::dispatch(new PayStart([], ['out_trade_no' => 'TEST-001']));

        $this->assertSame(['out_trade_no' => 'TEST-001'], $received);
    }

    public function testSdkLogIsWrittenToThinkPHP(): void
    {
        SdkLogger::info('payment test log', ['out_trade_no' => 'TEST-002']);
        $this->app->log->save();

        $file = __DIR__.'/fixtures/runtime/log/payment-test.log';

        $this->assertFileExists($file);
        $this->assertStringContainsString('payment test log', (string) file_get_contents($file));
    }

    public function testThinkRequestConvertsToPsrRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/pay/notify';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_POST = ['out_trade_no' => 'TEST-003'];

        $psr = payment_request($this->app->request);

        $this->assertSame('POST', $psr->getMethod());
        $this->assertSame('example.com', $psr->getUri()->getHost());
        $this->assertSame('/pay/notify', $psr->getUri()->getPath());
        $this->assertSame('TEST-003', $psr->getParsedBody()['out_trade_no']);
    }

    public function testDefaultConfigIsLoadedWithoutPublishedConfig(): void
    {
        Pay::clear();

        $app = new App(__DIR__.'/fixtures/default/');

        (new Service($app))->boot();

        $config = Pay::get(ConfigInterface::class);

        $this->assertSame('', $config->get('alipay.default.app_id'));
        $this->assertInstanceOf(Alipay::class, Pay::alipay());
    }

    public function testHelperReturnsProvider(): void
    {
        $this->assertInstanceOf(Alipay::class, payment('alipay'));
        $this->assertInstanceOf(Wechat::class, payment('wechat'));
    }

    public function testUnsupportedProviderThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        payment('unknown');
    }
}