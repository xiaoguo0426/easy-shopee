<?php


namespace Onetech\EasyShopee\AccessTokenServiceProvider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AccessTokenServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['access_token'] = function ($pimple) {

        };
    }
}