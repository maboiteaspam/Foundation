<?php
namespace C\Provider;

use C\FS\KnownFs;
use C\FS\KnownFsTagResolver;
use C\FS\Registry;
use C\FS\Store;
use C\Misc\ArrayHelpers;
use C\Watch\WatchedStore;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use C\ModernApp\File\Transforms as FileLayout;

/**
 * Class ModernAppServiceProvider
 * provides tools to compose a modern app
 *
 * @package C\Provider
 */
class ModernAppServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {

        // FS layout cache name
        if (!isset($app['modern.fs_store_name']))
            $app['modern.fs_store_name'] = "modern-layout-store";

        // declare a new layout FS,
        // to register the layouts of the module
        $app['modern.fs'] = $app->share(function(Application $app) {
            $store = $app['modern.fs_store_name'];
            $cache = $app['cache.get']($store);
            $registry = new Registry($store, $cache, [
                'basePath' => $app['project.path']
            ]);
            $registry->restrictWithExtensions([
                'yml',
            ]);
            return new KnownFs($registry);
        });

        // layout files content cache name
        if (!isset($app['modern.layout_store_name']))
            $app['modern.layout_store_name'] = "modern-layout-store";

        // declare a new cache for layout files content
        $app['modern.layout.store'] = $app->share(function (Application $app) {
            $store = new Store();
            $store->setFS($app['modern.fs']);

            $cache = $app['cache.get']($app['modern.layout_store_name']);
            $store->setCache($cache);

            return $store;
        });

        // attach layout files action helpers
        $app['modern.layout.helpers'] = $app->share(function (Application $app) {
            // @todo this should probably be moved away into separate service providers, for now on it s only inlined
            $helpers = new ArrayHelpers();
            $helpers->append(new \C\ModernApp\File\Helpers\LayoutHelper());
            $helpers->append(new \C\ModernApp\File\Helpers\AssetsHelper());
            $helper = new \C\ModernApp\File\Helpers\jQueryHelper();
            $helper->setGenerator($app['url_generator']);
            $helper->setRequest($app['request']);
            $helpers->append($helper);
            $helper = new \C\ModernApp\File\Helpers\EsiHelper();
            $helper->setGenerator($app['url_generator']);
            $helper->setRequest($app['request']);
            $helpers->append($helper);
            $helpers->append(new \C\ModernApp\File\Helpers\FileHelper());
            $helper = new \C\ModernApp\File\Helpers\DashboardHelper();
            $helper->setExtensions($app['modern.dashboard.extensions']);
            $helpers->append($helper);
            $helpers->append(new \C\ModernApp\File\Helpers\RequestHelper());
            return $helpers;
        });

        // attach dashboard extensions
        $app['modern.dashboard.extensions'] = $app->share(function (Application $app) {
            return [];
        });

        // provides layout transform able to load from a file
        $app['layout.file.transform'] = $app->protect(function (Application $app) {
            return FileLayout::transform()
                    ->setHelpers($app['modern.layout.helpers'])
                    ->setStore($app['modern.layout.store'])
                    ->setLayout($app['layout']);
        });
    }

    public function boot(Application $app)
    {
        // declare resources for a modern app
        if (isset($app['assets.fs'])) {
            $app['assets.fs']->register(__DIR__.'/../ModernApp/Dashboard/assets/', 'Dashboard');
            $app['assets.fs']->register(__DIR__.'/../ModernApp/jQuery/assets/', 'jQuery');
            $app['assets.fs']->register(__DIR__.'/../ModernApp/HTML/assets/', 'HTML');
        }
        if (isset($app['layout.fs'])) {
            $app['layout.fs']->register(__DIR__.'/../ModernApp/HTML/templates/', 'HTML');
            $app['layout.fs']->register(__DIR__.'/../ModernApp/Dashboard/templates/', 'Dashboard');
            $app['layout.fs']->register(__DIR__.'/../ModernApp/jQuery/templates/', 'jQuery');
        }
        if (isset($app['modern.fs'])) {
            $app['modern.fs']->register(__DIR__.'/../ModernApp/HTML/layouts/', 'HTML');
            $app['modern.fs']->register(__DIR__.'/../ModernApp/jQuery/layouts/', 'jQuery');
        }

        // a new tag computer is registered
        // to bind layouts to the cache system
        if (isset($app['httpcache.tagger'])) {
            $fs = $app['modern.fs'];
            $tagger = $app['httpcache.tagger'];
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->addTagComputer('modern.layout', new KnownFsTagResolver($fs));
        }

        // get app ready to run
        $app->before(function(Request $request, Application $app){
            if ($request->isXmlHttpRequest()) {
                $app['layout']->requestMatcher->setRequestKind('ajax');
            }
            $app['modern.fs']->registry->loadFromCache();
        }, Application::EARLY_EVENT);

        // register layout FS to the watcher system.
        // it will update the FS
        // it will also trigger layout files build
        // on application start and file change
        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->share(
                $app->extend('watchers.watched',
                    function($watched, Application $app) {
                        $w = new WatchedStore();
                        $w->setStore($app['modern.layout.store']);
                        $w->setRegistry($app['modern.fs']->registry);
                        $w->setName("modern.fs");
                        $watched[] = $w;
                        return $watched;
                    }
                )
            );
        }

    }
}
