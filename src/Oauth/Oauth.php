<?php


namespace Onetech\EasyShopee\Oauth;


use Pimple\Container;

/**
 * Class Oauth
 * @package Onetech\EasyShopee\Oauth
 * @property Authorizer $authorizer
 */
class Oauth
{
    /**
     * @var Container
     */
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function createAuthorizerApplication($token)
    {
        $this->fetch('authorizer_access_token', function (Authorizer $accessToken) use ($token) {
            //            $accessToken->setToken($token);
        });

        return $this->fetch('app', function ($app) use ($token) {
            //            $app['access_token'] = $this->fetch('authorizer_access_token');
        });
    }

    private function fetch($key, callable $callable = null)
    {
        $instance = $this->$key;

        if (! is_null($callable)) {
            $callable($instance);
        }

        return $instance;
    }

    /**
     * magic method.
     *
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->api, $method], $args);
    }

    /**
     * magic method.
     *
     * @param $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        $className = basename(str_replace('\\', '/', static::class));

        $name = strtolower($className) . '.' . $key;

        return $this->container->offsetGet($name);
    }
}