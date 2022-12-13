<?php


namespace Onetech\EasyShopee\Oauth;


use Hanson\Foundation\AbstractAPI;

class Api extends AbstractAPI
{

    /**
     * @var string
     */
    private string $app_key;

    /**
     * @var string
     */
    private string $app_secret;

    private string $sandbox;

    public const API_URL = 'https://api.lazada.com/rest';

    public function __construct($app_key, $app_secret, $sandbox)
    {
        $this->app_key = $app_key;
        $this->app_secret = $app_secret;
        $this->sandbox = $sandbox;
    }
}