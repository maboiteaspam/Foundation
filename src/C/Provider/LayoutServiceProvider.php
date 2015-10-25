<?php
namespace C\Provider;

use C\FS\KnownFs;
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
use C\View\Helper\FormViewHelper;
use Silex\Application;
use Silex\ServiceProviderInterface;
use C\Watch\WatchedRegistry;

class LayoutServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        LocalFs::$record = $app['debug'];

        $app['layout.factory'] = $app->protect(function() use($app) {
            $layout = new Layout();
            if ($app['debug']) $layout->enableDebug(true);
            if (isset($app['dispatcher'])) $layout->setDispatcher($app['dispatcher']);
            $layout->setContext($app['layout.view']);
            $layout->setFS($app['layout.fs']);


            $localMngr = $app['locale.manager'];

            $requestMatcher = new RequestTypeMatcher();
            $requestMatcher->setLang($localMngr->getLocale());
            $requestMatcher->setDevice('desktop');
            if (isset($app["mobile_detect"])) {
                if ($app["mobile_detect"]->isTablet()) {
                    $requestMatcher->setDevice('tablet');
                } elseif ($app["mobile_detect"]->isMobile()) {
                    $requestMatcher->setDevice('mobile');
                }
            }
            $layout->setRequestMatcher($requestMatcher);

            $layout->setLayoutSerializer($app['layout.serializer']);

            $layoutViewHelper = new LayoutViewHelper();
            $layoutViewHelper->setEnv($app['layout.env']);
            $layoutViewHelper->setLayout($layout);
            $layout->context->helpers->append($layoutViewHelper);

            return $layout;
        });
        $app['layout'] = $app->share(function() use($app) {
            return $app['layout.factory']();
        });

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

        $app['layout.view'] = $app->share(function() {
            return new Context();
        });

        $app['layout.view'] = $app->share($app->extend("layout.view", function(Context $view, Application $app) {
            $view->helpers->append($app['layout.helper.common']);
            return $view;
        }));

        $app['layout.helper.common'] = $app->share(function(Application $app) {
            $commonHelper = new CommonViewHelper();
            $commonHelper->setEnv($app['layout.env']);
            // see more about translator here http://stackoverflow.com/questions/25482856/basic-use-of-translationserviceprovider-in-silex
            if (isset($app['translator'])) {
                $commonHelper->setTranslator($app['translator']);
            }
            return $commonHelper;
        });

        $app['layout.view'] = $app->share($app->extend("layout.view", function(Context $view, Application $app) {
            $routingHelper = new RoutingViewHelper();
            $routingHelper->setEnv($app['layout.env']);
            $routingHelper->setUrlGenerator($app["url_generator"]);
            $view->helpers->append($routingHelper);
            return $view;
        }));

        $app['layout.view'] = $app->share($app->extend("layout.view", function(Context $view, Application $app) {
            $formHelper = new FormViewHelper();
            $formHelper->setEnv($app['layout.env']);
            $formHelper->setCommonHelper($app['layout.helper.common']);
            $view->helpers->append($formHelper);
            return $view;
        }));

        $app['layout.responder'] = $app->share(function(Application $app) {
            $responder = new LayoutResponder();
            if (isset($app['httpcache.tagger'])) {
                $responder = new TaggedLayoutResponder();
                $responder->setTagger($app['httpcache.tagger']);
            }
            return $responder;
        });

        $app['layout.serializer'] = $app->share(function (Application $app) {
            // @todo split across service providers
            $serializer = new LayoutSerializer();
            $serializer->setApp($app);
            if(isset($app["assets.fs"])) $serializer->setAssetsFS($app["assets.fs"]);
            if(isset($app["layout.fs"])) $serializer->setLayoutFS($app["layout.fs"]);
            if(isset($app["modern.fs"])) $serializer->setModernFS($app["modern.fs"]);
            return $serializer;
        });

        if (!isset($app['layout.cache_store_name']))
            $app['layout.cache_store_name'] = "layout-store";

        $app['layout.fs'] = $app->share(function(Application $app) {
            $storeName = $app['layout.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];

            $registry = new Registry('layout-', $cache, [
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
    /**
     *
     * @param Application $app Silex application instance.
     *
     * @return void
     **/
    public function boot(Application $app)
    {
        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->share($app->extend('watchers.watched', function($watched, Application $app) {
                $w = new WatchedRegistry();
                $w->setRegistry($app['layout.fs']->registry);
                $w->setName("layout.fs");
                $watched[] = $w;
                return $watched;
            }));
        }

        $app->before(function () use ($app) {
            $app['layout.fs']->registry->loadFromCache();
        });

        if (isset($app['httpcache.tagger'])) {
            $fs = $app['layout.fs'];
            $tagger = $app['httpcache.tagger'];
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->tagDataWith('template', function ($file) use($fs) {
                $template = $fs->get($file);
                $h = '';
                if ($template) {
                    $h .= $template['sha1'].$template['dir'].$template['name'];
                } else if(LocalFs::file_exists($file)) {
                    $h .= LocalFs::file_get_contents($file);
                } else {
                    // that is bad, it means we have registered files
                    // that does not exists
                    // or that can t be located back.
                    //
                    // you may have forgotten somewhere
                    // $app['layout.fs']->register(__DIR__.'/path/to/templates/', 'ModuleName');
                }
                return $h;
            });
        }
    }
}
