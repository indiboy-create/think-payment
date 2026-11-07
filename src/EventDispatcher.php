<?php

declare(strict_types=1);

namespace Youvision\ThinkPayment;

use Psr\EventDispatcher\EventDispatcherInterface;
use think\Event as ThinkEvent;

/**
 * 事件调度器
 *
 * 同时支持 SDK 自身的事件监听与 ThinkPHP 事件系统，
 * 因此可以在 app/event.php 中监听 Yansongda\Pay\Event 下的事件。
 *
 * 说明：SDK 的 EventDispatcherInterface 继承自 PSR-11，但额外约束了
 * addSubscriber 的 Symfony 类型；这里仅实现 PSR 接口，避免强制依赖 symfony。
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array<string, array<int, callable[]>> 事件名 => 优先级 => 监听器
     */
    protected array $listeners = [];

    public function __construct(protected ?ThinkEvent $thinkEvent = null)
    {
    }

    public function dispatch(object $event): object
    {
        foreach ($this->getListeners($event::class) as $listener) {
            $listener($event);
        }

        $this->thinkEvent?->trigger($event);

        return $event;
    }

    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->listeners[$eventName][$priority][] = $listener;
    }

    public function addSubscriber(object $subscriber): void
    {
        if (!method_exists($subscriber, 'getSubscribedEvents')) {
            return;
        }

        foreach ($subscriber::getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListener($eventName, [$subscriber, $params]);
                continue;
            }

            if (is_string($params[0])) {
                $this->addListener($eventName, [$subscriber, $params[0]], (int) ($params[1] ?? 0));
                continue;
            }

            foreach ($params as $listener) {
                $this->addListener($eventName, [$subscriber, $listener[0]], (int) ($listener[1] ?? 0));
            }
        }
    }

    public function removeListener(string $eventName, callable $listener): void
    {
        foreach ($this->listeners[$eventName] ?? [] as $priority => $listeners) {
            foreach ($listeners as $key => $registered) {
                if ($registered == $listener) {
                    unset($this->listeners[$eventName][$priority][$key]);
                }
            }

            if (empty($this->listeners[$eventName][$priority])) {
                unset($this->listeners[$eventName][$priority]);
            }
        }

        if (empty($this->listeners[$eventName])) {
            unset($this->listeners[$eventName]);
        }
    }

    public function removeSubscriber(object $subscriber): void
    {
        foreach ($this->listeners as $eventName => $priorities) {
            foreach ($priorities as $listeners) {
                foreach ($listeners as $listener) {
                    if (is_array($listener) && ($listener[0] ?? null) === $subscriber) {
                        $this->removeListener($eventName, $listener);
                    }
                }
            }
        }
    }

    public function getListeners(?string $eventName = null): array
    {
        if (null !== $eventName) {
            if (empty($this->listeners[$eventName])) {
                return [];
            }

            $priorities = $this->listeners[$eventName];
            krsort($priorities);

            return array_merge(...array_values($priorities));
        }

        $listeners = [];

        foreach (array_keys($this->listeners) as $name) {
            $listeners[$name] = $this->getListeners($name);
        }

        return $listeners;
    }

    public function getListenerPriority(string $eventName, callable $listener): ?int
    {
        foreach ($this->listeners[$eventName] ?? [] as $priority => $listeners) {
            foreach ($listeners as $registered) {
                if ($registered == $listener) {
                    return (int) $priority;
                }
            }
        }

        return null;
    }

    public function hasListeners(?string $eventName = null): bool
    {
        return null === $eventName
            ? !empty($this->listeners)
            : !empty($this->listeners[$eventName]);
    }
}