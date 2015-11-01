<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Class CacheProvider
 *
 * extends the original cache provider
 * to register a new cache drivers IncludeCache.
 *
 * IncludeCache is cool t use because the content
 * is easier to read than serialize, the original serializer.
 *
 * Although, it require to use a dirty hack
 * which consist of a str_replace of stdClass::__set_state to ''
 * so, it s not recommended to use it in production.
 *
 *
 * @package C\Provider
 */
class CacheProvider implements ServiceProviderInterface
{
    // extends cache.drivers service,
    // register the new cache driver include.
    public function register(Application $app)
    {
        if (isset($app['cache.drivers'])) {
            $app['cache.drivers'] = $app->extend('cache.drivers', function ($drivers) {
                $drivers['include'] = '\\C\\Cache\\IncludeCache';
                return $drivers;
            });
        }

        // provides a cache factory given a store name
        // it switches to default cache if the given store
        // is not configured
        $app['cache.get'] = $app->protect(function ($storeName) use ($app) {
            if (isset($app['caches'][$storeName]))
                $cache = $app['caches'][$storeName];
            else
                $cache = $app['cache'];

            return $cache;
        });
    }
    public function boot(Application $app) {}
}