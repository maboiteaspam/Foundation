<?php
namespace C\Provider;

use C\Form\FormFileLoader;
use C\Form\FormLayoutFileHelper;
use C\FS\KnownFs;
use C\FS\KnownFsTagResolver;
use C\FS\Registry;
use C\FS\Store;
use C\Misc\ArrayHelpers;
use C\View\Context;
use C\Watch\WatchedStore;
use Silex\Application;
use Silex\ServiceProviderInterface;


/**
 * Class FormServiceProvider
 * provides form capabilities among the framework.
 *
 * It declares a new form FS, forms.fs,
 * to declare forms as yml files.
 *
 * It injects helpers into the view context
 * to support form display
 *
 *
 *
 * @package C\Provider
 */
class FormServiceProvider implements ServiceProviderInterface
{
    /**
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        // FS forms cache name
        if (!isset($app['forms.fs.cache_store_name']))
            $app['forms.fs.cache_store_name'] = "forms-fs-store";

        // declare a new form FS,
        // to register the forms of the module as yml files
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

        // form files content cache name
        if (!isset($app['forms.cache_store_name']))
            $app['forms.cache_store_name'] = "forms-store";

        // declare a new cache for form files content
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
        // inject pre defined intl messages from symfony
        // as Validation:/ and Form:/
        if (isset($app['intl.fs'])) {
            $fs = $app['intl.fs'];
            /* @var $fs KnownFS */
            if (isset($app['validator'])) {
                try{
                    $r = new \ReflectionClass('Symfony\Component\Validator\Validation');
                    $path = dirname($r->getFilename()).'/Resources/translations/';
                    $fs->register($path, 'Validator');
                }catch(\Exception $ex) {}
            }

            if (isset($app['form.factory'])) {
                try{
                    $r = new \ReflectionClass('Symfony\Component\Form\Form');
                    $path = dirname($r->getFilename()).'/Resources/translations/';
                    $fs->register($path, 'Form');
                }catch(\Exception $ex) {}
            }
        }

        // attach a new layout helper
        // to load forms from layout yml files
        if (isset($app['modern.layout.helpers'])) {
            $app['modern.layout.helpers'] = $app->share(
                $app->extend("modern.layout.helpers",
                    function (ArrayHelpers $helpers, Application $app) {
                        $layoutHelper = new FormLayoutFileHelper();
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

        // attach a new view context helper
        // to display forms
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

        // provide the assets consumed
        // by the form to submit and display errors
        if (isset($app['assets.fs'])) {
            $app['assets.fs']->register(__DIR__.'/../Form/assets/', 'Form');
        }

        if (isset($app['modern.fs'])) {
            $app['modern.fs']->register(__DIR__.'/../Form/layouts/', 'Form');
        }

        // get ready to display a page
        $app->before(function($request, Application $app){
            $app['forms.fs']->registry->loadFromCache();
        }, Application::EARLY_EVENT);

        // a new tag computer is registered
        // to bind forms into the cache system
        if (isset($app['httpcache.tagger'])) {
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $fs = $app['forms.fs'];
            $tagger = $app['httpcache.tagger'];
            $tagger->addTagComputer('forms', new KnownFsTagResolver($fs));
        }

        // register forms FS to the watcher system.
        // it will update ths FS
        // it will also trigger form files build
        // on application start and file change
        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->share(
                $app->extend('watchers.watched',
                    function($watched, Application $app) {
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
