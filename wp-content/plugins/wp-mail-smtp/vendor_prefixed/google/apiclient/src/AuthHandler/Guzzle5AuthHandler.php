<?php

namespace WPMailSMTP\Vendor\Google\AuthHandler;

use WPMailSMTP\Vendor\Google\Auth\CredentialsLoader;
use WPMailSMTP\Vendor\Google\Auth\HttpHandler\HttpHandlerFactory;
use WPMailSMTP\Vendor\Google\Auth\FetchAuthTokenCache;
use WPMailSMTP\Vendor\Google\Auth\Subscriber\AuthTokenSubscriber;
use WPMailSMTP\Vendor\Google\Auth\Subscriber\ScopedAccessTokenSubscriber;
use WPMailSMTP\Vendor\Google\Auth\Subscriber\SimpleSubscriber;
use WPMailSMTP\Vendor\GuzzleHttp\Client;
use WPMailSMTP\Vendor\GuzzleHttp\ClientInterface;
use WPMailSMTP\Vendor\Psr\Cache\CacheItemPoolInterface;
/**
*
*/
class Guzzle5AuthHandler
{
    protected $cache;
    protected $cacheConfig;
    public function __construct(\WPMailSMTP\Vendor\Psr\Cache\CacheItemPoolInterface $cache = null, array $cacheConfig = [])
    {
        $this->cache = $cache;
        $this->cacheConfig = $cacheConfig;
    }
    public function attachCredentials(\WPMailSMTP\Vendor\GuzzleHttp\ClientInterface $http, \WPMailSMTP\Vendor\Google\Auth\CredentialsLoader $credentials, callable $tokenCallback = null)
    {
        // use the provided cache
        if ($this->cache) {
            $credentials = new \WPMailSMTP\Vendor\Google\Auth\FetchAuthTokenCache($credentials, $this->cacheConfig, $this->cache);
        }
        return $this->attachCredentialsCache($http, $credentials, $tokenCallback);
    }
    public function attachCredentialsCache(\WPMailSMTP\Vendor\GuzzleHttp\ClientInterface $http, \WPMailSMTP\Vendor\Google\Auth\FetchAuthTokenCache $credentials, callable $tokenCallback = null)
    {
        // if we end up needing to make an HTTP request to retrieve credentials, we
        // can use our existing one, but we need to throw exceptions so the error
        // bubbles up.
        $authHttp = $this->createAuthHttp($http);
        $authHttpHandler = \WPMailSMTP\Vendor\Google\Auth\HttpHandler\HttpHandlerFactory::build($authHttp);
        $subscriber = new \WPMailSMTP\Vendor\Google\Auth\Subscriber\AuthTokenSubscriber($credentials, $authHttpHandler, $tokenCallback);
        $http->setDefaultOption('auth', 'google_auth');
        $http->getEmitter()->attach($subscriber);
        return $http;
    }
    public function attachToken(\WPMailSMTP\Vendor\GuzzleHttp\ClientInterface $http, array $token, array $scopes)
    {
        $tokenFunc = function ($scopes) use($token) {
            return $token['access_token'];
        };
        $subscriber = new \WPMailSMTP\Vendor\Google\Auth\Subscriber\ScopedAccessTokenSubscriber($tokenFunc, $scopes, $this->cacheConfig, $this->cache);
        $http->setDefaultOption('auth', 'scoped');
        $http->getEmitter()->attach($subscriber);
        return $http;
    }
    public function attachKey(\WPMailSMTP\Vendor\GuzzleHttp\ClientInterface $http, $key)
    {
        $subscriber = new \WPMailSMTP\Vendor\Google\Auth\Subscriber\SimpleSubscriber(['key' => $key]);
        $http->setDefaultOption('auth', 'simple');
        $http->getEmitter()->attach($subscriber);
        return $http;
    }
    private function createAuthHttp(\WPMailSMTP\Vendor\GuzzleHttp\ClientInterface $http)
    {
        return new \WPMailSMTP\Vendor\GuzzleHttp\Client(['base_url' => $http->getBaseUrl(), 'defaults' => ['exceptions' => \true, 'verify' => $http->getDefaultOption('verify'), 'proxy' => $http->getDefaultOption('proxy')]]);
    }
}
