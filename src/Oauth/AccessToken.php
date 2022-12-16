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
    protected int $shop_id;
    protected string $main_account_id;

    protected $cacheKey;
    protected $cacheRefreshKey;

    protected $cacheShopIdKey;

    public function __construct(Foundation $app)
    {
        $this->app_key = $app->getConfig('app_key') ?? '';
        $this->app_secret = $app->getConfig('app_secret') ?? '';
        $this->sandbox = $app->getConfig('sandbox') ?? false;

        $this->tokenJsonKey = 'access_token';
        $this->expiresJsonKey = 'expire_in';

        $this->cacheKey = 'shp-access::' . $this->app_key . '::';
        $this->cacheRefreshKey = 'shp-refresh-access::' . $this->app_key . '::';

        $this->cacheShopIdKey = 'shp-shopId::' . $this->app_key . '::';

        $this->setAppId($this->app_key);
        $this->setSecret($this->app_secret);

        parent::__construct($app);
    }

    /**
     * @throws ShopeeException
     * @return mixed
     */
    public function getTokenFromServer()
    {
        $uri = '/api/v2/auth/token/get';

        return (new Api($this->app_key, $this->app_secret, $this->sandbox))->post($uri, [
            'code' => $this->code,
            'partner_id' => $this->app_key,
            'shop_id' => $this->shop_id,
//            'main_account_id' => $this->main_account_id
        ]);
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
            throw new ShopeeException(sprintf('Shopee API Error: [%s] %s', $result['error'], $result['message']));
        }
        $this->setRefreshToken($result);
    }

    private function getCacheRefreshKey()
    {
        return $this->cacheRefreshKey . $this->appId;
    }

    public function setRefreshToken($result)
    {
        $refresh_token = $result['refresh_token'];
        $this->getCache()->save($this->getCacheRefreshKey(), $refresh_token);

        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->getCache()->fetch($this->getCacheRefreshKey()) ?: '';
    }

    private function getCacheShopIdKey()
    {
        return $this->cacheShopIdKey . $this->appId;
    }

    public function setShopIdCache(int $shop_id)
    {
        $this->getCache()->save($this->getCacheShopIdKey(), $shop_id);
    }

    public function getShopIdCache(): int
    {
        return $this->getCache()->fetch($this->getCacheShopIdKey()) ?: '';
    }

    /**
     * @throws ShopeeException
     * @throws TokenException
     */
    public function refresh(string $refresh_token = ''): string
    {

        if ($refresh_token === '') {
            //使用默认存储方式获取
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
}