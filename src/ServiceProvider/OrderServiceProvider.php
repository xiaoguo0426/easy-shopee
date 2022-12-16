<?php


namespace Onetech\EasyShopee\ServiceProvider;


use Onetech\EasyShopee\Application\Order;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class OrderServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['order'] = function ($pimple) {
            return new Order($pimple->access_token->getToken(), $pimple->getConfig('app_key'), $pimple->getConfig('app_secret'), $pimple->access_token->getShopIdCache(), $pimple->getConfig('sandbox'));
        };
    }
}