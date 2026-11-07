<?php

declare(strict_types=1);

namespace Youvision\ThinkPayment;

use Closure;
use Psr\Container\ContainerInterface;
use think\App;
use Youvision\ThinkPayment\Exception\ContainerNotFoundException;

/**
 * ThinkPHP 容器适配器
 *
 * yansongda/artful 需要 PSR-11 容器，并依赖 set()/make() 两个扩展方法，
 * ThinkPHP 的 think\Container 没有 set()，因此通过本适配器做一层转接。
 */
class Container implements ContainerInterface
{
    public function __construct(protected App $app)
    {
    }

    public function get(string $id): mixed
    {
        if ($this->has($id)) {
            return $this->app->make($id);
        }

        throw new ContainerNotFoundException("容器中不存在服务: {$id}");
    }

    public function has(string $id): bool
    {
        return $this->app->bound($id)
            || $this->app->exists($id)
            || class_exists($id);
    }

    /**
     * 注册服务实例、闭包或类名绑定
     */
    public function set(string $id, mixed $value): void
    {
        if ($value instanceof Closure) {
            $this->app->bind($id, $value);

            return;
        }

        if (is_string($value) && (class_exists($value) || interface_exists($value))) {
            $this->app->bind($id, $value);

            return;
        }

        $this->app->instance($id, $value);
    }

    /**
     * 创建实例
     */
    public function make(string $id, array $parameters = []): mixed
    {
        return $this->app->make($id, $parameters);
    }
}