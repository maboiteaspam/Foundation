<?php
namespace C\Provider;

use C\Form\FormFileLoader;
use C\FS\KnownFs;
use C\FS\Registry;
use C\FS\Store;
use C\Misc\ArrayHelpers;
use C\ModernApp\File\Helpers\FormViewHelper;
use C\View\Context;
use C\Watch\WatchedStore;
use Silex\Application;
use Silex\ServiceProviderInterface;

class FormServiceProvider implements ServiceProviderInterface
{
    /**
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        if (!isset($app['forms.fs.cache_store_name']))
            $app['forms.fs.cache_store_name'] = "forms-fs-store";

        $app['forms.fs'] = $app->share(function(Application $app) {
            $storeName = $app['forms.fs.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];

            $registry = new Registry('forms-', $cache, [
                'basePath' => $app['project.path']
            ]);
            $registry->restrictWithExtensions([
                'yml',
            ]);
            return new KnownFs($registry);
        });

        if (!isset($app['forms.cache_store_name']))
            $app['forms.cache_store_name'] = "forms-store";

        $app['forms.store'] = $app->share(function (Application $app) {
            $store = new Store();
            $store->setFS($app['forms.fs']);

            $storeName = $app['forms.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            $store->setCache($cache);

            return $store;
        });
    }
    /**
     *
     * @param Application $app Silex application instance.
     *
     * @return void
     **/
    public function boot(Application $app)
    {
        if (isset($app['intl.fs'])) {
            $fs = $app['intl.fs'];
            /* @var $fs KnownFS */
            if (isset($app['validator'])) {
                $r = new \ReflectionClass('Symfony\Component\Validator\Validation');
                $path = dirname($r->getFilename()).'/Resources/translations/';
                $fs->register($path, 'Validator');
            }

            if (isset($app['form.factory'])) {
                $r = new \ReflectionClass('Symfony\Component\Form\Form');
                $path = dirname($r->getFilename()).'/Resources/translations/';
                $fs->register($path, 'Form');
            }

            if (isset($app['modern.layout.helpers'])) {
                $app['modern.layout.helpers'] = $app->share(
                    $app->extend("modern.layout.helpers",
                        function (ArrayHelpers $helpers, Application $app) {
                            $layoutHelper = new FormViewHelper();
                            $layoutHelper->setFactory($app['form.factory']);
                            $layoutHelper->setUrlGenerator($app['url_generator']);
                            $formFileLoader = new FormFileLoader();
                            if (isset($app['form.factory'])) {
                                $formFileLoader->setFactory($app['form.factory']);
                            }
                            if (isset($app['forms.store'])) {
                                $formFileLoader->setStore($app['forms.store']);
                            }
                            $layoutHelper->setFormLoader($formFileLoader);
                            $helpers->append($layoutHelper);
                            return $helpers;
                        }
                    )
                );
            }
        }

        if (isset($app['layout.view'])) {
            $viewHelper = new \C\View\Helper\FormViewHelper();
            $app['layout.view'] = $app->share(
                $app->extend("layout.view",
                    function(Context $view, Application $app) use($viewHelper) {
                        $viewHelper->setEnv($app['layout.env']);
                        $viewHelper->setCommonHelper($app['layout.helper.common']);
                        $view->helpers->append($viewHelper);
                        return $view;
                    }
                )
            );
            $app->before(function($request) use($viewHelper){
                $viewHelper->setRequest($request);
            });
        }

        if (isset($app['assets.fs'])) {
            $app['assets.fs']->register(__DIR__.'/../Form/assets/', 'Form');
        }

        if (isset($app['modern.fs'])) {
            $app['modern.fs']->register(__DIR__.'/../Form/layouts/', 'Form');
        }

        $app->before(function($request, Application $app){
            $app['forms.fs']->registry->loadFromCache();
        }, Application::EARLY_EVENT);

        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->share(
                $app->extend('watchers.watched', function($watched, Application $app) {
                    $w = new WatchedStore();
                    $w->setStore($app['forms.store']);
                    $w->setRegistry($app['forms.fs']->registry);
                    $w->setName("forms.fs");
                    $watched[] = $w;
                    return $watched;
                }
                )
            );
        }
    }
}
