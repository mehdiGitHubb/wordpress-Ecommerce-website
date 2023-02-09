<?php

namespace WPMailSMTP\Vendor\Google\AuthHandler;

use WPMailSMTP\Vendor\Google\Auth\CredentialsLoader;
use WPMailSMTP\Vendor\Google\Auth\HttpHandler\HttpHandlerFactory;
use WPMailSMTP\Vendor\Google\Auth\FetchAuthTokenCache;
use WPMailSMTP\Vendor\Google\Auth\Middleware\AuthTokenMiddleware;
use WPMailSMTP\Vendor\Google\Auth\Middleware\ScopedAccessTokenMiddleware;
use WPMailSMTP\Vendor\Google\Auth\Middleware\SimpleMiddleware;
use WPMailSMTP\Vendor\GuzzleHttp\Client;
use WPMailSMTP\Vendor\GuzzleHttp\ClientInterface;
use WPMailSMTP\Vendor\Psr\Cache\CacheItemPoolInterface;
/**
* This supports Guzzle 6
*/
class Guzzle6AuthHandler
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
        $middleware = new \WPMailSMTP\Vendor\Google\Auth\Middleware\AuthTokenMiddleware($credentials, $authHttpHandler, $tokenCallback);
        $config = $http->getConfig();
        $config['handler']->remove('google_auth');
        $config['handler']->push($middleware, 'google_auth');
        $config['auth'] = 'google_auth';
        $http = new \WPMailSMTP\Vendor\GuzzleHttp\Client($config);
        return $http;
    }
    public function attachToken(\WPMailSMTP\Vendor\GuzzleHttp\ClientInterface $http, array $token, array $scopes)
    {
        $tokenFunc = function ($scopes) use($token) {
            return $token['access_token'];
        };
        $middleware = new \WPMailSMTP\Vendor\Google\Auth\Middleware\ScopedAccessTokenMiddleware($tokenFunc, $scopes, $this->cacheConfig, $this->cache);
        $config = $http->getConfig();
        $config['handler']->remove('google_auth');
        $config['handler']->push($middleware, 'google_auth');
        $config['auth'] = 'scoped';
        $http = new \WPMailSMTP\Vendor\GuzzleHttp\Client($config);
        return $http;
    }
    public function attachKey(\WPMailSMTP\Vendor\GuzzleHttp\ClientInterface $http, $key)
    {
        $middleware = new \WPMailSMTP\Vendor\Google\Auth\Middleware\SimpleMiddleware(['key' => $key]);
        $config = $http->getConfig();
        $config['handler']->remove('google_auth');
        $config['handler']->push($middleware, 'google_auth');
        $config['auth'] = 'simple';
        $http = new \WPMailSMTP\Vendor\GuzzleHttp\Client($config);
        return $http;
    }
    private function createAuthHttp(\WPMailSMTP\Vendor\GuzzleHttp\ClientInterface $http)
    {
        return new \WPMailSMTP\Vendor\GuzzleHttp\Client(['base_uri' => $http->getConfig('base_uri'), 'http_errors' => \true, 'verify' => $http->getConfig('verify'), 'proxy' => $http->getConfig('proxy')]);
    }
}
