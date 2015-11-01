<?php
namespace C\Provider;

use C\HTTP\RequestTagResolver;
use C\Misc\Utils;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use C\TagableResource\ResourceTagger;
use C\HTTP\Store;
use C\HTTP\HttpCache;

/**
 * Class HttpCacheServiceProvider
 * provides an http based caching system.
 *
 * It provides
 * - 'resource to tag' mechanism to control the cache and its data
 * - http cache facade to speak http cache
 * - http cache store
 *
 * @package C\Provider
 */
class HttpCacheServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // provides 'resource to tag' computer capabilities
        $app['httpcache.tagger'] = $app->share(function() {
            return new ResourceTagger();
        });

        // the name of the http cache content
        if (!isset($app['httpcache.cache_store_name']))
            $app['httpcache.cache_store_name'] = "http-store";

        // the http cache store,
        // it contains http response content, headers and meta
        $app['httpcache.store'] = $app->share(function(Application $app) {
            $store = $app['httpcache.cache_store_name'];
            $cache = $app['cache.get']($store);
            $store = new Store($store, $cache);
            return $store;
        });
        // the application taggedResource value.
//        $app['httpcache.taggedResource'] = null;

        // defines an http cache facade
        // it helps determine what and how to cache
        $app['httpcache.facade'] = $app->share(function() {
            return new HttpCache();
        });
    }

    public function boot(Application $app)
    {

        // This handler register tag computers
        // to consume jit http values
        $app->before(function (Request $request, Application $app) {
            if (isset($app['httpcache.tagger'])) {
                $tagger = $app['httpcache.tagger'];

                /* @var $fs \C\FS\KnownFs */
                /* @var $tagger \C\TagableResource\ResourceTagger */

                // inject parameters from request object.
                $tagger->addTagComputer('request', new RequestTagResolver($request));

                // inject the computed user locale.
                if (isset($app['locale.manager'])) {
                    $tagger->addTagComputer('jit-locale', function ($value) use($app) {
                        /* @var $localeMngr \C\Intl\LocaleManager */
                        $localeMngr = $app['locale.manager'];
                        return $localeMngr->getLocale();
                    });
                } else {
                    $tagger->addTagComputer('jit-locale', function ($value) use($app) {
                        /* @var $request Request */
                        $request = $app['request'];
                        return $request->getLocale();
                    });
                }

                // inject the user device.
                $tagger->addTagComputer('jit-device', function ($value) use($app) {
                    /* @var $layout \C\Layout\Layout */
                    $layout = $app['layout'];
                    return $layout->requestMatcher->deviceType;
                });

                // inject the request kind (ajax, esi, get, ect)
                $tagger->addTagComputer('jit-request-kind', function ($value) use($app) {
                    /* @var $layout \C\Layout\Layout */
                    $layout = $app['layout'];
                    return $layout->requestMatcher->requestKind;
                });

                // inject accept content type negotiation.
                $tagger->addTagComputer('jit-accept', function ($value) use($app) {
                    // @todo, check how to deal with HTTP accept request header.
                    return null;
                });

                // inject debug value.
                $tagger->addTagComputer('jit-debug', function ($value) use($app) {
                    return $app['debug']?'with-debug':'without-debug';
                });
            }

            Utils::stderr('-------------');
            Utils::stderr('receiving url '.$request->getUri());

        }, Application::EARLY_EVENT);

        // before the app is executed, we should check the cache
        // and try to take a shortcut.
        $app->before(function (Request $request, Application $app) {
            // it is not good to cache a non GET method.
            if ($request->isMethodSafe()) {
                /* @var $tagger ResourceTagger */
                /* @var $store Store */
                /* @var $cache HttpCache */
                $cache = $app['httpcache.facade'];
                $cache->setRequest($app['request']);
                $cache->setStore($app['httpcache.store']);
                $cache->setTagger($app['httpcache.tagger']);

                $checkFreshness = $app['httpcache.check_taged_resource_freshness'];

                // when the request is sent by user
                // it may contain an if-none-match: header
                // which means the user is looking for an url page he already saw before,
                // he knows its etag.
                // We should check the cache to know how to handle this request
                // within the best response time possible.
                $resultResponse = $cache->getFirstResponseMatchingByEtag($checkFreshness);
                if ($resultResponse!==false) {
                    Utils::stderr("found valid etag");
                    $resultResponse->setNotModified();
                    return $resultResponse;
                }

                $etags = $cache->getProperEtags();
                // here can exists a FPC cache layer.
                // using url+ua+lang+request kind.
                if(!count($etags) && false) {
                    Utils::stderr('no etag in this request');
                    // @todo check if resource explicitly wants fresh version
                    // when user press ctl+f5, it sends request with max-age=0 (+/-),
                    // it means the user wants fresh version of the document.
                    // so we should not call cache here.
                    $resultResponse = $cache->getResponseMatchingByUri($checkFreshness);
                    if ($resultResponse!==false) {
                        Utils::stderr("found valid etag");
//                        $resultResponse->setNotModified();
//                        dump($resultResponse);
                        return $resultResponse;
                    }
                }
            }
            return null;
        }, Application::LATE_EVENT);


        // once app has finished,
        // let s check if the response is cache-able.
        // It must not be
        // a cached response itself,
        // neither use an unsafe method (POST)
        // neither be 304 response code
        // and the system should have resource tager enabled.
        // If everything looks good,
        // lets record that into the cache store.
        $app->after(function (Request $request, Response $response, Application $app) {

            /* @var $tagger ResourceTagger */
            /* @var $store Store */
            /* @var $cache HttpCache */
            $cache = $app['httpcache.facade'];
            $cache->setRequest($app['request']);
            $cache->setStore($app['httpcache.store']);
            $cache->setTagger($app['httpcache.tagger']);

            Utils::stderr('is response cache-able '.var_export($cache->isCacheAble($response), true));
            Utils::stderr('response code '.var_export($response->getStatusCode(), true));
            Utils::stderr('response is from cache '.var_export($response->headers->has("X-CACHED"), true));

            /* @var $layout \C\Layout\Layout */
            $layout = $app['layout'];
            $TaggedResource = $layout->getTaggedResource();

            if ($TaggedResource
                && $request->isMethodSafe()
                && $cache->isCacheAble($response)) {
                    Utils::stderr('saving resource '.$request->getUri());
                    $cache->storeResponseToCache($response, $TaggedResource);
//                    Utils::stderr('headers ='.json_encode($headers));
            }
            $response->headers->remove("X-CACHED");
        }, Application::LATE_EVENT);


    }
}