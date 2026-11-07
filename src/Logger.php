<?php

declare(strict_types=1);

namespace Youvision\ThinkPayment;

use Psr\Log\AbstractLogger;
use think\Log as ThinkLog;
use Yansongda\Artful\Contract\LoggerInterface;

/**
 * 日志适配器
 *
 * 将 yansongda/pay 的 PSR-3 日志写入 ThinkPHP 日志体系。
 */
class Logger extends AbstractLogger implements LoggerInterface
{
    public function __construct(
        protected ThinkLog $log,
        protected ?string $channel = null
    ) {
    }

    public function log($level, $message, array $context = []): void
    {
        $logger = $this->channel ? $this->log->channel($this->channel) : $this->log;

        $logger->log($level, (string) $message, $context);
    }
}