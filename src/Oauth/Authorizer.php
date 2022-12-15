<?php


namespace Onetech\EasyShopee\Oauth;


use Onetech\EasyShopee\Shopee;

class Authorizer
{

    const OAUTH_URL = 'https://partner.shopeemobile.com/api/v2/shop/auth_partner?sign=%s&partner_id=%s&timestamp=%s&redirect=%s';

    const SANDBOX_OAUTH_URL = 'https://partner.test-stable.shopeemobile.com/api/v2/shop/auth_partner?sign=%s&partner_id=%s&timestamp=%s&redirect=%s';

    private string $app_key;

    private string $redirect_uri;

    private bool $sandbox;

    private string $sign;
    private int $timestamp;

    /**
     * @throws \Exception
     */
    public function __construct(Shopee $shopee)
    {
        $this->app_key = $shopee->getConfig('app_key') ?? '';
        $app_secret = $shopee->getConfig('app_secret') ?? '';
        $this->redirect_uri = urlencode($shopee->getConfig('redirect_uri') ?? '');
        $this->sandbox = $shopee->getConfig('sandbox') ?? false;

        $uri = '/api/v2/shop/auth_partner';

        $signature = new Signature($this->app_key, $app_secret);
        $this->sign = $signature->gen($uri);
        $this->timestamp = $signature->timestamp;
    }

    /**
     * 授权链接
     * @return string
     */
    public function create(): string
    {
        return sprintf($this->sandbox ? self::SANDBOX_OAUTH_URL : self::OAUTH_URL, $this->sign, $this->app_key, $this->timestamp, $this->redirect_uri);
    }

}