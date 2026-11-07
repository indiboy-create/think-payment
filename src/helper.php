<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use think\Request as ThinkRequest;
use Yansongda\Pay\Pay;
use Youvision\ThinkPayment\Request;

if (!function_exists('payment')) {
    /**
     * 获取支付渠道实例
     *
     * @param string $provider 渠道：alipay、wechat、unipay、jsb、douyin、paypal、stripe
     */
    function payment(string $provider = 'alipay'): mixed
    {
        $providers = ['alipay', 'wechat', 'unipay', 'jsb', 'douyin', 'paypal', 'stripe'];

        if (!in_array($provider, $providers, true)) {
            throw new InvalidArgumentException("不支持的支付渠道: {$provider}");
        }

        return Pay::{$provider}();
    }
}

if (!function_exists('payment_request')) {
    /**
     * 将 ThinkPHP 请求转换为 PSR-7 请求，用于常驻内存模式下的支付回调
     */
    function payment_request(?ThinkRequest $request = null): ServerRequestInterface
    {
        return Request::toPsr($request ?: app('request'));
    }
}