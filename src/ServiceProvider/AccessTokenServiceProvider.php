<?php


namespace Onetech\EasyShopee\ServiceProvider;


use Hanson\Foundation\Foundation;
use Onetech\EasyShopee\Oauth\AccessToken;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AccessTokenServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['access_token'] = function (Foundation $pimple) {
            return new AccessToken($pimple);
        };
    }
}