<?php


namespace Onetech\EasyShopee\Oauth;


use Hanson\Foundation\AbstractAccessToken;
use Hanson\Foundation\Foundation;
use Onetech\EasyShopee\Exception\InvalidArgumentsException;
use Onetech\EasyShopee\Exception\ShopeeException;
use Onetech\EasyShopee\Exception\TokenException;

class AccessToken extends AbstractAccessToken
{

    private int $app_key;
    private string $app_secret;
    private bool $sandbox;

    protected string $code;
    private ?int $shop_id;
    private ?int $main_account_id;

    protected $cacheKey;
    private string $cacheRefreshKey;

    private string $cacheShopIdKey;
    private string $cacheShopIdListKey;
    private string $cacheMerchantIdKey;
    private string $cacheMerchantRefreshKey;

    public function __construct(Foundation $app)
    {
        $this->app_key = $app->getConfig('app_key') ?? '';
        $this->app_secret = $app->getConfig('app_secret') ?? '';
        $this->sandbox = $app->getConfig('sandbox') ?? false;

        $this->tokenJsonKey = 'access_token';
        $this->expiresJsonKey = 'expire_in';

        $this->cacheKey = 'shp-access::' . $this->app_key;
        $this->cacheRefreshKey = 'shp-refresh-access::' . $this->app_key;
        $this->cacheMerchantRefreshKey = 'shp-merchant_refresh-access::' . $this->app_key;

        $this->cacheShopIdKey = 'shp-shop-id::' . $this->app_key;
        $this->cacheShopIdListKey = 'shp-shop-id-list::' . $this->app_key;
        $this->cacheMerchantIdKey = 'shp-merchant-id-list::' . $this->app_key;

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
     * @throws InvalidArgumentsException
     * @throws ShopeeException
     * @throws TokenException
     * @return string|null
     */
    public function getToken($forceRefresh = false): ?string
    {
        if (true === $forceRefresh) {
            $result = $this->getTokenFromServer();
            $this->checkTokenResponse($result);
            $this->setToken(
                $token = $result[$this->tokenJsonKey],
                $this->expiresJsonKey ? $result[$this->expiresJsonKey] : null
            );

            return $token;
        }

        if (false === $forceRefresh) {
            $token = $this->getCache()->fetch($this->getCacheKey());
            if (empty($token)) {
                throw new TokenException("access_token doesn't exist");
            }
            return $token;
        }

        throw new InvalidArgumentsException('Invalid Argument');
    }

    /**
     * 生成Token
     * @throws ShopeeException
     */
    public function genToken(): string
    {

        $result = $this->getTokenFromServer();

        $this->checkTokenResponse($result);

        $token = $result[$this->tokenJsonKey];
        $expires = $this->expiresJsonKey ? $result[$this->expiresJsonKey] : null;
        $refresh_token = $result['refresh_token'];

        if (isset($result['merchant_id_list'])) {
            $merchant_id_list = $result['merchant_id_list'];
            $this->setMerchantIdListCache($merchant_id_list);
            foreach ($merchant_id_list as $merchant_id) {
                $this->setMerchantRefreshToken($merchant_id, $refresh_token);
            }
        }

        if (isset($result['shop_id_list'])) {
            $shop_id_list = $result['shop_id_list'];
            $this->setShopIdListCache($shop_id_list);

            foreach ($shop_id_list as $shop_id) {
                $this->setRefreshToken($shop_id, $refresh_token);
                $this->storageToken($shop_id, $token, $expires);
            }
        }

        return $token;
    }

    /**
     * 获取access_token
     * @throws TokenException
     */
    public function fetchToken(int $shop_id)
    {
        $token = $this->getCache()->fetch($this->genCacheKey($shop_id));
        if (empty($token)) {
            throw new TokenException("access_token doesn't exist");
        }
        return $token;
    }

    /**
     * 存储access_token
     * @param int $shop_id
     * @param string $token
     * @param int $expires
     * @return $this
     */
    public function storageToken(int $shop_id, string $token, int $expires = 86400): AccessToken
    {
        $this->getCache()->save($this->genCacheKey($shop_id), $token, $expires);
        $this->token = $token;

        return $this;
    }

    /**
     * @param int $shop_id
     * @return string
     */
    public function genCacheKey(int $shop_id): string
    {
        return $this->getCacheKey() . "::$shop_id";
    }


    /**
     * @throws ShopeeException
     */
    public function checkTokenResponse($result)
    {
        if (! empty($result['error'])) {
            throw new ShopeeException(sprintf('Shopee API Error: [%s] %s', $result['error'], $result['message'] ?? ''));
        }
    }

    private function getCacheRefreshKey(int $shop_id): string
    {
        return $this->cacheRefreshKey . "::$shop_id";
    }

    private function getCacheMerchantRefreshKey(int $merchant_id): string
    {
        return $this->cacheMerchantRefreshKey . "::$merchant_id";
    }

    public function setRefreshToken(int $shop_id, string $refresh_token): void
    {
        $this->getCache()->save($this->getCacheRefreshKey($shop_id), $refresh_token);
    }

    public function setMerchantRefreshToken(int $merchant_id, string $refresh_token): void
    {
        $this->getCache()->save($this->getCacheMerchantRefreshKey($merchant_id), $refresh_token);
    }

    public function getRefreshToken(int $shop_id): string
    {
        return $this->getCache()->fetch($this->getCacheRefreshKey($shop_id)) ?: '';
    }

    public function getMerchantRefreshToken(int $merchant_id): string
    {
        return $this->getCache()->fetch($this->getCacheMerchantRefreshKey($merchant_id)) ?: '';
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

    private function getCacheMerchantIdListKey(): string
    {
        return $this->cacheMerchantIdKey;
    }

    private function getCacheShopIdListKey(): string
    {
        return $this->cacheShopIdListKey;
    }

    /**
     * 设置shop id list缓存(Main Account授权)
     */
    public function setShopIdListCache(array $shop_id_list): void
    {
        try {
            $this->getCache()->save($this->getCacheShopIdListKey(), json_encode($shop_id_list, JSON_THROW_ON_ERROR));
        } catch (\JsonException $exception) {
        }
    }

    /**
     * 获取shop id list缓存(Main Account授权)
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

    public function setMerchantIdListCache(array $merchant_id_list): void
    {
        try {
            $this->getCache()->save($this->getCacheMerchantIdListKey(), json_encode($merchant_id_list, JSON_THROW_ON_ERROR));
        } catch (\JsonException $exception) {
        }
    }

    public function getMerchantIdListCache(): array
    {
        $cache = $this->getCache()->fetch($this->getCacheMerchantIdListKey()) ?: '[]';
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
    public function refreshShop(int $shop_id, string $refresh_token = ''): string
    {

        if ($refresh_token === '') {
            //使用默认存储方式获取
            $refresh_token = $this->getRefreshToken($shop_id);
            if (! $refresh_token) {
                throw new TokenException('refresh token not exist.');
            }
        }

        $response = (new Api($this->app_key, $this->app_secret, $this->sandbox))->post('/api/v2/auth/access_token/get', [
            'refresh_token' => $refresh_token,
            'partner_id' => $this->getAppId(),
            'shop_id' => $shop_id
        ]);

        $this->checkTokenResponse($response);

        $this->setRefreshToken($shop_id, $response['refresh_token']);
        $this->storageToken($shop_id, $token = $response[$this->tokenJsonKey], $this->expiresJsonKey ? $response[$this->expiresJsonKey] : null);

        return $token;
    }

    /**
     * @throws ShopeeException
     * @throws TokenException
     */
    public function refreshMerchant(int $merchant_id, string $refresh_token = ''): string
    {

        if ($refresh_token === '') {
            //使用默认存储方式获取
            $refresh_token = $this->getMerchantRefreshToken($merchant_id);
            if (! $refresh_token) {
                throw new TokenException('refresh token not exist.');
            }
        }

        $response = (new Api($this->app_key, $this->app_secret, $this->sandbox))->post('/api/v2/auth/access_token/get', [
            'refresh_token' => $refresh_token,
            'partner_id' => $this->getAppId(),
            'merchant_id' => $merchant_id
        ]);

        $this->checkTokenResponse($response);

        $this->setMerchantRefreshToken($merchant_id, $response['refresh_token']);
        $this->storageToken($merchant_id, $token = $response[$this->tokenJsonKey], $this->expiresJsonKey ? $response[$this->expiresJsonKey] : null);

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

    public function checkToken(int $shop_id): bool
    {
        return (bool) $this->getCache()->fetch($this->genCacheKey($shop_id));
    }

}