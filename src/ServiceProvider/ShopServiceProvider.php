<?php


namespace Onetech\EasyShopee\ServiceProvider;


use Onetech\EasyShopee\Application\Shop;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ShopServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['shop'] = function ($pimple) {
            return new Shop($pimple->access_token, $pimple->getConfig('app_key'), $pimple->getConfig('app_secret'), $pimple->access_token->getShopIdCache(), $pimple->getConfig('sandbox'));
        };
    }
}