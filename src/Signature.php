<?php


namespace Onetech\EasyShopee;


class Signature
{

    private string $access_token;
    public int $timestamp;

    private string $app_key;
    private string $app_secret;
    private int $shop_id;

    public function __construct(string $access_token, string $app_key, string $app_secret, int $shop_id)
    {

        $this->access_token = $access_token;
        $this->timestamp = time();

        $this->app_key = $app_key;
        $this->app_secret = $app_secret;
        $this->shop_id = $shop_id;
    }

    public function gen(string $uri): string
    {
        $base_str = sprintf('%s%s%s%s%s', $this->app_key, $uri, $this->timestamp, $this->access_token, $this->shop_id);
        return hash_hmac('sha256', $base_str, $this->app_secret);
    }
}