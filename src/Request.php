<?php

declare(strict_types=1);

namespace Youvision\ThinkPayment;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use think\Request as ThinkRequest;

/**
 * ThinkPHP 请求转换器
 *
 * yansongda/pay 的回调验签需要 PSR-7 请求。FPM 模式下 SDK 会自动读取超全局变量，
 * 常驻内存（Swoole、Workerman 等）模式下需要显式传入当前请求。
 */
class Request
{
    public static function toPsr(ThinkRequest $request): ServerRequestInterface
    {
        $psr = new ServerRequest(
            $request->method(),
            $request->url(true),
            $request->header(),
            $request->getContent(),
            '1.1',
            $request->server()
        );

        return $psr
            ->withQueryParams((array) $request->get())
            ->withParsedBody((array) $request->post())
            ->withCookieParams((array) $request->cookie());
    }
}