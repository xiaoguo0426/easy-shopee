<?php


namespace Onetech\EasyShopee\Oauth;


class Signature
{
    public int $timestamp;

    private string $app_key;
    private string $app_secret;

    public function __construct(string $app_key,string $app_secret)
    {
        $this->timestamp = time();

        $this->app_key = $app_key;
        $this->app_secret = $app_secret;
    }

    public function gen(string $uri): string
    {
        $base_str = sprintf('%s%s%s', $this->app_key, $uri, $this->timestamp);
        return hash_hmac('sha256', $base_str, $this->app_secret);
    }
}