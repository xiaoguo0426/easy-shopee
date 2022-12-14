<?php


namespace Onetech\EasyShopee\Oauth;


use Hanson\Foundation\AbstractAccessToken;
use Hanson\Foundation\Foundation;
use Onetech\EasyShopee\Exception\ShopeeException;
use Onetech\EasyShopee\Exception\TokenException;

class AccessToken extends AbstractAccessToken
{

    protected int $app_key;
    protected string $app_secret;
    protected bool $sandbox;

    protected string $code;
    protected ?int $shop_id;
    protected ?int $main_account_id;

    protected $cacheKey;
    protected string $cacheRefreshKey;

    protected string $cacheShopIdKey;
    protected string $cacheShopIdListKey;
    protected string $cacheMerchantIdKey;

    public function __construct(Foundation $app)
    {
        $this->app_key = $app->getConfig('app_key') ?? '';
        $this->app_secret = $app->getConfig('app_secret') ?? '';
        $this->sandbox = $app->getConfig('sandbox') ?? false;

        $this->tokenJsonKey = 'access_token';
        $this->expiresJsonKey = 'expire_in';

        $this->cacheKey = 'shp-access::' . $this->app_key;
        $this->cacheRefreshKey = 'shp-refresh-access::' . $this->app_key;

        $this->cacheShopIdKey = 'shp-shop-id::' . $this->app_key;
        $this->cacheShopIdListKey = 'shp-shop-id-list::' . $this->app_key;
        $this->cacheMerchantIdKey = 'shp-merchant-account-id::' . $this->app_key;

        $this->setAppId($this->app_key);
        $this->setSecret($this->app_secret);

        $this->shop_id = null;
        $this->main_account_id = null;

        $this->setCache($app->getConfig('cache'));

        parent::__construct($app);
    }

    /**
     * @throws ShopeeException
     * @return mixed
     */
    public function getTokenFromServer()
    {
        $uri = '/api/v2/auth/token/get';

        return (new Api($this->app_key, $this->app_secret, $this->sandbox))->post($uri, array_merge([
            'code' => $this->code,
            'partner_id' => $this->app_key,
        ], $this->shop_id ? ['shop_id' => $this->shop_id,] : ['main_account_id' => $this->main_account_id]));
    }

    /**
     * @param false $forceRefresh
     * @throws ShopeeException
     * @return string
     */
    public function getToken($forceRefresh = false): string
    {
        $cached = $this->getCache()->fetch($this->getCacheKey()) ?: $this->token;
        if ($forceRefresh || empty($cached)) {

            $result = $this->getTokenFromServer();

            $this->checkTokenResponse($result);

            $this->setToken(
                $token = $result[$this->tokenJsonKey],
                $this->expiresJsonKey ? $result[$this->expiresJsonKey] : null
            );

            return $token;
        }

        return $cached;
    }

    /**
     * @throws ShopeeException
     */
    public function checkTokenResponse($result)
    {
        if (! empty($result['error'])) {
            throw new ShopeeException(sprintf('Shopee API Error: [%s] %s', $result['error'], $result['message'] ?? ''));
        }
        $this->setRefreshToken($result);
        if (isset($result['merchant_id_list'])) {
            $shift = array_shift($result['merchant_id_list']);
            $shift && $this->setMerchantIdCache($shift);
            $result['shop_id_list'] && $this->setShopIdListCache($result['shop_id_list']);
        }
    }

    private function getCacheRefreshKey(): string
    {
        return $this->cacheRefreshKey;
    }

    public function setRefreshToken($result): AccessToken
    {
        $refresh_token = $result['refresh_token'];
        $this->getCache()->save($this->getCacheRefreshKey(), $refresh_token);

        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->getCache()->fetch($this->getCacheRefreshKey()) ?: '';
    }

    private function getCacheShopIdKey(): string
    {
        return $this->cacheShopIdKey;
    }

    public function setShopIdCache(int $shop_id): void
    {
        $this->getCache()->save($this->getCacheShopIdKey(), $shop_id);
    }

    public function getShopIdCache(): ?int
    {
        return $this->getCache()->fetch($this->getCacheShopIdKey()) ?: null;
    }

    public function setMerchantIdCache(int $main_account_id): void
    {
        $this->getCache()->save($this->getCacheMerchantIdKey(), $main_account_id);
    }

    public function getMerchantIdCache(): ?int
    {
        return $this->getCache()->fetch($this->getCacheMerchantIdKey()) ?: null;
    }

    private function getCacheMerchantIdKey(): string
    {
        return $this->cacheMerchantIdKey;
    }

    private function getCacheShopIdListKey(): string
    {
        return $this->cacheShopIdListKey;
    }

    /**
     * ??????shop id list??????(Main Account??????)
     */
    public function setShopIdListCache(array $shop_id_list): void
    {
        try {
            $this->getCache()->save($this->getCacheShopIdListKey(), json_encode($shop_id_list, JSON_THROW_ON_ERROR));
        } catch (\JsonException $exception) {
        }
    }

    /**
     * ??????shop id list??????(Main Account??????)
     */
    public function getShopIdListCache()
    {
        $cache = $this->getCache()->fetch($this->getCacheShopIdListKey()) ?: '[]';
        try {
            return json_decode($cache, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            return [];
        }
    }

    /**
     * @throws ShopeeException
     * @throws TokenException
     */
    public function refreshShop(string $refresh_token = ''): string
    {

        if ($refresh_token === '') {
            //??????????????????????????????
            $refresh_token = $this->getRefreshToken();
            if (! $refresh_token) {
                throw new TokenException('refresh token not exist.');
            }
        }

        $response = (new Api($this->app_key, $this->app_secret, $this->sandbox))->post('/api/v2/auth/access_token/get', [
            'refresh_token' => $refresh_token,
            'partner_id' => $this->getAppId(),
            'shop_id' => $this->getShopIdCache()
        ]);

        $this->checkTokenResponse($response);

        $token = $response[$this->tokenJsonKey];
        $this->setToken(
            $token,
            $this->expiresJsonKey ? $response[$this->expiresJsonKey] : null
        );

        return $token;
    }

    /**
     * @throws ShopeeException
     * @throws TokenException
     */
    public function refreshMerchant(string $refresh_token = ''): string
    {

        if ($refresh_token === '') {
            //??????????????????????????????
            $refresh_token = $this->getRefreshToken();
            if (! $refresh_token) {
                throw new TokenException('refresh token not exist.');
            }
        }
        $response = (new Api($this->app_key, $this->app_secret, $this->sandbox))->post('/api/v2/auth/access_token/get', [
            'refresh_token' => $refresh_token,
            'partner_id' => $this->getAppId(),
            'merchant_id' => $this->getMerchantIdCache()
        ]);

        $this->checkTokenResponse($response);

        $token = $response[$this->tokenJsonKey];
        $this->setToken(
            $token,
            $this->expiresJsonKey ? $response[$this->expiresJsonKey] : null
        );

        return $token;
    }

    public function setCode(string $code): AccessToken
    {
        $this->code = $code;
        return $this;
    }

    public function setShopId(int $shop_id): AccessToken
    {
        $this->shop_id = $shop_id;
        $this->setShopIdCache($shop_id);
        return $this;
    }

    public function setMainAccountId(int $main_account_id): AccessToken
    {
        $this->main_account_id = $main_account_id;
        return $this;
    }

    public function checkToken(): bool
    {
        return (bool) $this->getCache()->fetch($this->getCacheKey());
    }

}