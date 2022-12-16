<?php


namespace Onetech\EasyShopee\ServiceProvider;


use Onetech\EasyShopee\Oauth\Authorizer;
use Onetech\EasyShopee\Oauth\Oauth;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class OauthServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['oauth'] = function ($pimple) {
            return new Oauth($pimple);
        };

        $pimple['oauth.authorizer'] = function ($pimple) {
            return new Authorizer($pimple);
        };
    }
}