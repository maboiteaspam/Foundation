<?php
namespace C\Provider;

use C\Assets\AssetsInjector;
use C\Assets\BuiltinResponder;
use C\FS\KnownFsTagResolver;
use C\FS\Registry;
use C\FS\LocalFs;
use C\FS\KnownFs;
use C\Assets\Bridger;
use C\Misc\ArrayHelpers;
use C\View\Helper\AssetsViewHelper;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use C\Watch\WatchedRegistry;

/**
 * Class AssetsServiceProvider
 * enhance layout capability to work with assets
 * it will use the cache systems
 * and connect to the cache layer
 *
 * It provides a new FS assets.fs, to register assets.
 *
 * @package C\Provider
 */
class AssetsServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        LocalFs::$record = $app['debug'];

        // Project relative path to www public directory
        if (!isset($app['assets.www_path']))
            $app['assets.www_path'] = 'www/';

        // Set the web server type
        // http / nginx / builtin
        if (!isset($app['assets.bridge_type']))
            $app['assets.bridge_type'] = 'builtin';

        // the file of the path containing bridge instructions
        // for the web server type
        if (!isset($app['assets.bridge_file_path']))
            $app['assets.bridge_file_path'] = '.assets_bridge';

        // Add a bridger service to generate teh bridge file
        $app['assets.bridger'] = $app->share(function() {
            return new Bridger();
        });

        // FS assets cache name
        if (!isset($app['assets.cache_store_name']))
            $app['assets.cache_store_name'] = "assets-store";

        // declare a new asset FS,
        // to register the assets of the module
        $app['assets.fs'] = $app->share(function(Application $app) {

            $store = $app['assets.cache_store_name'];
            $cache = $app['cache.get']($store);

            $registry = new Registry($store, $cache, [
                'basePath' => $app['project.path']
            ]);

            // file extensions this fs will remember
            $registry->restrictWithExtensions([
                'css',
                'js',
                'woff',
                'otf'//...ect
            ]);

            return new KnownFs($registry);
        });


    }
    public function boot(Application $app)
    {

        // Add an static asset responder
        // If the system detects the responder,
        // it will try to make use of it.
        if ($app['assets.bridge_type']==='builtin') {
            $app['assets.responder'] = $app->share(function(Application $app) {
                $responder = new BuiltinResponder();
                $responder->wwwDir = $app['documentRoot'];
                $responder->setFS($app['assets.fs']);
                return $responder;
            });
            if(!isset($app['assets.verbose'])) $app['assets.verbose'] = false;
        }


        // register a new tag computer
        // to bind assets into the cache system
        if (isset($app['httpcache.tagger'])) {
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $fs     = $app['assets.fs'];
            $tagger = $app['httpcache.tagger'];
            $tagger->addTagComputer('asset', new KnownFsTagResolver($fs));
        }

        // register a new layout file helper
        //  structure:
        //      - import: Module:/layout.yml
        if (isset($app['modern.layout.helpers'])) {
            $app['modern.layout.helpers'] = $app->extend('modern.layout.helpers',
                $app->share(
                    function (ArrayHelpers $helpers) use($app) {
                        $helpers->append(new \C\Assets\AssetsLayoutFileHelper());
                        return $helpers;
                    }
                )
            );
        }

        // layout render helper to
        // transform the asset directives
        // into concrete html components
        if (isset($app['layout'])) {
            $app->before(function (Request $request, Application $app) {

                $injector = new AssetsInjector();
                $injector->concatenate  = $app['assets.concat'];
                $injector->assetsFS     = $app['assets.fs'];
                $injector->wwwDir       = $app['assets.www_dir'];
                $injector->buildDir     = $app['assets.build_dir'];

                $layout = $app['layout'];
                /* @var $layout \C\Layout\Layout */
                $layout->afterResolve(function () use($injector, $app) {
                    $injector->applyInlineAssets($app['layout']);
                });
                $layout->afterResolve(function () use($injector, $app) {
                    $injector->applyAssetsRequirements($app['layout']);
                });
                $layout->beforeRender(function () use($injector, $app) {
                    $injector->applyFileAssets($app['layout']);
                });
                if ($injector->concatenate) {
                    $app->after(function() use($injector, $app){
                        $injector->createMergedAssetsFiles($app['layout']);
                    }, Application::LATE_EVENT);
                }
            });
        }

        // Register a new view helper
        // it enhance the view context
        // with new methods to work with assets
        if (isset($app['layout.view'])) {
            $assetsViewHelper = new AssetsViewHelper();
            $assetsViewHelper->setPatterns($app["assets.patterns"]);
            $app['layout.view']->addHelper($assetsViewHelper);
        }

        // consume the asset responder to serve assets
        if (php_sapi_name()==='cli-server') {
            if (isset($app['assets.responder'])) {
                /* @var $responder \C\Assets\BuiltinResponder */
                $responder = $app['assets.responder'];
                $app['assets.fs']->registry->loadFromCache();
                $responder->respond($app['assets.verbose']);
            }
        } else {
            // get ready to display a page
            $app->before(function($request, Application $app){
                $app['assets.fs']->registry->loadFromCache();
            }, Application::EARLY_EVENT);
        }

        // register asset FS to the watcher system.
        // it will watch assets and triggers assets build,
        // currently only the file system is supported.
        // later sass, requirejs should have some handles here.
        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->share(
                $app->extend('watchers.watched',
                    function($watched, Application $app) {
                        $w = new WatchedRegistry();
                        $w->setRegistry($app['assets.fs']->registry);
                        $w->setName("assets.fs");
                        $watched[] = $w;
                        return $watched;
                    }
                )
            );
        }

    }
}