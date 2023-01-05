<?php


namespace Onetech\EasyShopee;


use Hanson\Foundation\Foundation;
use Onetech\EasyShopee\Application\Order;
use Onetech\EasyShopee\Application\Returns;
use Onetech\EasyShopee\Application\Shop;
use Onetech\EasyShopee\ServiceProvider\AccessTokenServiceProvider;
use Onetech\EasyShopee\ServiceProvider\OauthServiceProvider;
use Onetech\EasyShopee\Oauth\AccessToken;
use Onetech\EasyShopee\Oauth\Oauth;
use Onetech\EasyShopee\ServiceProvider\OrderServiceProvider;
use Onetech\EasyShopee\ServiceProvider\ReturnsServiceProvider;
use Onetech\EasyShopee\ServiceProvider\ShopServiceProvider;

/**
 * Class Shopee
 * @package Onetech\EasyShopee
 * @property AccessToken $access_token
 * @property Oauth $oauth
 * @property Order $order
 * @property Returns $returns
 * @property Shop $shop
 */
class Shopee extends Foundation
{
    protected $providers = [
        AccessTokenServiceProvider::class,
        OauthServiceProvider::class,
        OrderServiceProvider::class,
        ReturnsServiceProvider::class,
        ShopServiceProvider::class,
    ];
}