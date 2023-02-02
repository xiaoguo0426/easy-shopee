<?php


namespace Onetech\EasyShopee\ServiceProvider;


use Onetech\EasyShopee\Application\Order;
use Onetech\EasyShopee\Application\Returns;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ReturnsServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['returns'] = function ($pimple) {
            return new Returns($pimple->access_token, $pimple->getConfig('app_key'), $pimple->getConfig('app_secret'), $pimple->access_token->getShopIdCache(), $pimple->getConfig('sandbox'));
        };
    }
}