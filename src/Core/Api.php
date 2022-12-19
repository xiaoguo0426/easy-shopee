<?php


namespace Onetech\EasyShopee\Core;


use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Hanson\Foundation\AbstractAPI;
use Onetech\EasyShopee\Exception\ShopeeException;
use Onetech\EasyShopee\Signature;

class Api extends AbstractAPI
{

    public const TOKEN_API_DOMAIN = 'https://partner.shopeemobile.com';
    public const SANDBOX_TOKEN_API_DOMAIN = 'https://partner.test-stable.shopeemobile.com';

    /**
     * @var string
     */
    private string $access_token;

    /**
     * @var string
     */
    private string $app_key;

    /**
     * @var string
     */
    private string $app_secret;

    private int $shop_id;

    private bool $sandbox;

    public function __construct(string $access_token, string $app_key, string $app_secret, int $shop_id, bool $sandbox)
    {
        $this->access_token = $access_token;
        $this->app_key = $app_key;
        $this->app_secret = $app_secret;
        $this->shop_id = $shop_id;
        $this->sandbox = $sandbox;
    }

    /**
     * @param string $uri
     * @param string $method
     * @param $params
     * @throws ShopeeException
     * @return mixed
     */
    public function request(string $uri, string $method, $params)
    {
        $signature = new Signature($this->access_token, $this->app_key, $this->app_secret, $this->shop_id);
        $sign = $signature->gen($uri);
        $timestamp = $signature->timestamp;

        $url = sprintf(($this->sandbox ? self::SANDBOX_TOKEN_API_DOMAIN : self::TOKEN_API_DOMAIN) . $uri . '?partner_id=%s&timestamp=%s&access_token=%s&shop_id=%s&sign=%s', $this->app_key, $timestamp, $this->access_token, $this->shop_id, $sign);

        $client = new HttpClient();
        try {
            if ($method === 'POST') {
                $res = $client->request('POST', $url, [
                    'json' => $params
                ]);
            } else {
                $url .= '&' . http_build_query($params);
                $res = $client->get($url, []);
            }
        } catch (GuzzleException $guzzleException) {
            $contents = $guzzleException->getResponse()->getBody()->getContents();
            $result = json_decode($contents, true);
            throw new ShopeeException(sprintf('Shopee API Error: [%s] %s', $result['error'], $result['message']));
        }

        try {
            return json_decode($res->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            throw new ShopeeException('Shopee API Response Data Format Invalid. ' . $jsonException->getMessage());
        }
    }

    /**
     *
     * @param $uri
     * @param $params
     * @throws ShopeeException
     * @return mixed
     */
    public function post($uri, $params)
    {
        return $this->request($uri, 'POST', $params);
    }

    /**
     *
     * @param string $uri
     * @param array $params
     * @throws ShopeeException
     * @return mixed
     */
    public function get(string $uri, array $params)
    {
        return $this->request($uri, 'GET', $params);
    }
}