<?php
namespace C\Provider;

use C\FS\KnownFs;
use C\FS\KnownFsTagResolver;
use C\FS\LocalFs;
use C\FS\Registry;

use C\Layout\Layout;
use C\Layout\Responder\LayoutResponder;
use C\Layout\Responder\TaggedLayoutResponder;
use C\Layout\Misc\LayoutSerializer;
use C\Layout\Misc\RequestTypeMatcher;
use C\View\Env;
use C\View\Context;
use C\View\Helper\CommonViewHelper;
use C\View\Helper\LayoutViewHelper;
use C\View\Helper\RoutingViewHelper;
use Silex\Application;
use Silex\ServiceProviderInterface;
use C\Watch\WatchedRegistry;

/**
 * Class LayoutServiceProvider
 * provides core mechanism
 * to compose and render a view
 *
 * @package C\Provider
 */
class LayoutServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        LocalFs::$record = $app['debug'];

        // provides a factory to create layout objects,
        // as Layout is the backbone which delimits both concerns
        // it is tightly linked to underlying modules
        // about http, translation and some more.
        // Use this factory to get an insitu Layout object,
        // in context of the current request treatment.
        $app['layout.factory'] = $app->protect(function() use($app) {
            $layout = new Layout();
            if ($app['debug']) $layout->enableDebug(true);
            if (isset($app['dispatcher'])) $layout->setDispatcher($app['dispatcher']);
            $layout->setContext($app['layout.view']);
            $layout->setFS($app['layout.fs']);


            $requestMatcher = new RequestTypeMatcher();
            if (isset($app['locale.manager'])) {
                $localMngr = $app['locale.manager'];
                $requestMatcher->setLang($localMngr->getLocale());
            }
            $requestMatcher->setDevice('desktop');
            if (isset($app["mobile_detect"])) {
                if ($app["mobile_detect"]->isTablet()) {
                    $requestMatcher->setDevice('tablet');
                } elseif ($app["mobile_detect"]->isMobile()) {
                    $requestMatcher->setDevice('mobile');
                }
            }
            $layout->setRequestMatcher($requestMatcher);

            $layoutViewHelper = new LayoutViewHelper();
            $layoutViewHelper->setEnv($app['layout.env']);
            $layoutViewHelper->setLayout($layout);
            $layout->context->helpers->append($layoutViewHelper);

            return $layout;
        });

        // Defines the current layout object for the current request
        $app['layout'] = $app->share(function() use($app) {
            return $app['layout.factory']();
        });

        // defines environmental configuration
        // to impact visual rendering of the views
        $app['layout.env.charset'] = 'utf-8';
        $app['layout.env.date_format'] = '';
        $app['layout.env.timezone'] = '';
        $app['layout.env.number_format'] = '';
        $app['layout.env'] = $app->share(function(Application $app) {
            $env = new Env();
            $env->setCharset($app['layout.env.charset']);
            $env->setDateFormat($app['layout.env.date_format']);
            $env->setTimezone($app['layout.env.timezone']);
            $env->setNumberFormat($app['layout.env.number_format']);
            return $env;
        });

        // defines the view context within what the views are rendered.
        // it provides the templates with the ability
        // of a dynamically enhanced $this object of helpers methods
        $app['layout.view'] = $app->share(function() {
            return new Context();
        });

        // register the common view helper
        // on the view context
        $app['layout.view'] = $app->share(
            $app->extend("layout.view",
                function(Context $view, Application $app) {
                    $view->helpers->append($app['layout.helper.common']);
                    return $view;
                }
            )
        );

        // provides common view context helper
        // it provides translations, encoding, text helpers
        $app['layout.helper.common'] = $app->share(function(Application $app) {
            $commonHelper = new CommonViewHelper();
            $commonHelper->setEnv($app['layout.env']);
            if (isset($app['translator'])) {
                $commonHelper->setTranslator($app['translator']);
            }
            return $commonHelper;
        });


        // provides route view context helper
        // to generate urls against application defined routes
        $app['layout.view'] = $app->share(
            $app->extend("layout.view",
                function(Context $view, Application $app) {
                    $routingHelper = new RoutingViewHelper();
                    $routingHelper->setEnv($app['layout.env']);
                    $routingHelper->setUrlGenerator($app["url_generator"]);
                    $view->helpers->append($routingHelper);
                    return $view;
                }
            )
        );

        // provides the layout responder object
        // to wire the layout object
        // to the http implementation
        $app['layout.responder'] = $app->share(function(Application $app) {
            $responder = new LayoutResponder();
            if (isset($app['httpcache.tagger'])) {
                $responder = new TaggedLayoutResponder();
                $responder->setTagger($app['httpcache.tagger']);
            }
            return $responder;
        });

        // the name of the cache to store
        // templates fs
        if (!isset($app['layout.cache_store_name']))
            $app['layout.cache_store_name'] = "layout-store";

        // declare a new templates FS,
        // to register the templates of the module
        $app['layout.fs'] = $app->share(function(Application $app) {
            $store = $app['layout.cache_store_name'];
            $cache = $app['cache.get']($store);

            $registry = new Registry($store, $cache, [
                'basePath' => $app['project.path']
            ]);
            $registry->restrictWithExtensions([
                'php',
            ]);
            return new KnownFs($registry);
        });

//        if (isset($app['form.extensions'])) {
            // @todo dig more about form framework...
//            $app['form.extensions'] = $app->share($app->extend('form.extensions', function ($extensions) use ($app) {
//                $extensions[] = new CoreExtension();
//                return $extensions;
//            }));
//        }
    }

    public function boot(Application $app)
    {
        // register templates FS to the watcher system.
        // it will watch templates and triggers fs build.
        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->share(
                $app->extend('watchers.watched',
                    function($watched, Application $app) {
                        $w = new WatchedRegistry();
                        $w->setRegistry($app['layout.fs']->registry);
                        $w->setName("layout.fs");
                        $watched[] = $w;
                        return $watched;
                    }
                )
            );
        }

        // get ready to display a page
        $app->before(function () use ($app) {
            $app['layout.fs']->registry->loadFromCache();
        });

        // register a new tag computer
        // to bind templates into the cache system
        if (isset($app['httpcache.tagger'])) {
            $fs = $app['layout.fs'];
            $tagger = $app['httpcache.tagger'];
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->addTagComputer('template', new KnownFsTagResolver($fs));
        }
    }
}
