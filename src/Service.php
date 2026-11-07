<?php

declare(strict_types=1);

namespace Youvision\ThinkPayment;

use think\Service as ThinkService;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Contract\EventDispatcherInterface;
use Yansongda\Artful\Contract\LoggerInterface;
use Yansongda\Artful\Event\ArtfulEnd;
use Yansongda\Artful\Event\ArtfulStart;
use Yansongda\Artful\Event\HttpEnd;
use Yansongda\Artful\Event\HttpStart;
use Yansongda\Pay\Pay;
use Yansongda\Pay\PayListener;

/**
 * ThinkPHP 8 支付服务
 *
 * 负责把 yansongda/pay 接入 ThinkPHP 的容器、事件与日志体系。
 */
class Service extends ThinkService
{
    /**
     * 启动服务
     */
    public function boot(): void
    {
        $config = $this->resolveConfig();

        // 使用 ThinkPHP 容器替换 SDK 默认容器，使 SDK 内部依赖可以从应用容器解析
        Artful::setContainer(new Container($this->app));

        // 将 SDK 事件桥接到 ThinkPHP 事件系统，可在 app/event.php 中监听
        $dispatcher = new EventDispatcher($this->app->event);
        Artful::set(EventDispatcherInterface::class, $dispatcher);

        // 强制刷新配置，兼容 ThinkPHP 常驻内存运行模式下的重复启动
        Pay::config(array_merge($config, ['_force' => true]));

        // 若项目安装了 symfony/event-dispatcher，SDK 会用自己的调度器覆盖上面的实例，
        // 这里恢复桥接调度器并补回 SDK 内部依赖的监听器
        if (Artful::get(EventDispatcherInterface::class) !== $dispatcher) {
            Artful::set(EventDispatcherInterface::class, $dispatcher);

            $dispatcher->addListener(ArtfulStart::class, [PayListener::class, 'artfulStart']);
            $dispatcher->addListener(ArtfulEnd::class, [PayListener::class, 'artfulEnd']);
            $dispatcher->addListener(HttpStart::class, [PayListener::class, 'httpStart']);
            $dispatcher->addListener(HttpEnd::class, [PayListener::class, 'httpEnd']);
        }

        // 日志统一写入 ThinkPHP 日志通道
        if (!empty($config['logger']['enable'])) {
            Artful::set(LoggerInterface::class, new Logger(
                $this->app->log,
                $config['logger']['channel'] ?? null
            ));
        }
    }

    /**
     * 读取并合并支付配置
     */
    protected function resolveConfig(): array
    {
        $default = require dirname(__DIR__).'/config/payment.php';

        $config = $this->app->config->get('payment', []);

        if (!is_array($config)) {
            $config = [];
        }

        return array_replace_recursive($default, $config);
    }
}