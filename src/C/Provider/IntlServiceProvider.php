<?php
namespace C\Provider;

use C\FS\KnownFs;
use C\FS\LocalFs;
use C\FS\Registry;

use C\Intl\IntlFileLoader;
use C\Intl\IntlJitLoader;
use C\Intl\LocaleManager;
use C\Intl\Translator;
use C\Intl\XliffIntlLoader;
use C\Intl\YmlIntlLoader;

use Moust\Silex\Cache\CacheInterface;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use C\Watch\WatchedIntl;

class IntlServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Application $app
     **/
    public function register(Application $app)
    {
        LocalFs::$record = $app['debug'];

        if (!isset($app['intl.cache_store_name']))
            $app['intl.cache_store_name'] = "intl-fs-store";

        $app['intl.fs'] = $app->share(function(Application $app) {
            $storeName = $app['intl.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            return new KnownFs(new Registry('intl-', $cache, [
                'basePath' => $app['project.path']
            ]));
        });

        if (!isset($app['intl-content.cache_store_name']))
            $app['intl-content.cache_store_name'] = "intl-content-store";

        $app['intl-content.cache'] = $app->share(function(Application $app) {
            $storeName = $app['intl-content.cache_store_name'];
            if (isset($app['caches'][$storeName])) $cache = $app['caches'][$storeName];
            else $cache = $app['cache'];
            return $cache;
        });

        $app['intl.loader'] = $app->share(function(Application $app) {
            $loader = new IntlFileLoader();
            $loader->addLoader(new YmlIntlLoader());
            $loader->addLoader(new XliffIntlLoader());
            return $loader;
        });
        $app['intl.jitloader'] = $app->share(function(Application $app) {
            /* @var $mngr LocaleManager */
            /* @var $cache CacheInterface */
            $manager = $app['locale.manager'];
            $cache = $app['intl-content.cache'];
            $jitLoader = new IntlJitLoader();
            $jitLoader->setCache($cache);
            $jitLoader->setLocaleManager($manager);
            return $jitLoader;
        });


        $app['locale'] = 'en';
        $app['locale.manager'] = $app->share(function(Application $app) {
            return new LocaleManager();
        });

        $app['translator'] = $app->share(function(Application $app) {
            $translator = new Translator(null, $app['debug']);
            $translator->setJitLoader($app['intl.jitloader']);
            return $translator;
        });
        if (!isset($app['locale_fallbacks'])) $app['locale_fallbacks'] = array('en');
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
                $w = new WatchedIntl();
                $w->setRegistry($app['intl.fs']->registry);
                $w->setLoader($app['intl.loader']);
                $w->setJitLoader($app['intl.jitloader']);
                $w->setName("intl");
                $watched[] = $w;
                return $watched;
            }));
        }

        /**
         * Prepare and configure Translation service to consume.
         */
        $app->before(function (Request $request) use ($app) {
            // load all translations from cache.
            $app['intl.fs']->registry->loadFromCache();

            if (isset($app['locale.manager'])) {
                // configure the locale manager
                $localMngr = $app['locale.manager'];
                /* @var $localMngr \C\Intl\LocaleManager */
                $localMngr->setFallbackLocales($app['locale_fallbacks']);

                // compute the best locale between
                // available locales in the system
                // and user locale provided in his request.
                /* @var $jitLoader \C\Intl\IntlJitLoader */
                $jitLoader = $app['intl.jitloader'];
                $knownLocales = $jitLoader->fetchWellKnownLocales();

                $reqLng = $request->getPreferredLanguage(null);
                if (!$reqLng) $reqLng = $app['locale_fallbacks'][0];

                $locale = $localMngr->computeLocales ($knownLocales, $reqLng);
                if ($locale) {
                    $locale = $locale[0];
                    $localMngr->setLocale($locale);
                } else {
                    // ouch. That s a kind of problem..
                }
            }
        });

        if (isset($app['httpcache.tagger'])) {
            $fs = $app['intl.fs'];
            $tagger = $app['httpcache.tagger'];
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->tagDataWith('intl', function ($intl) use($fs) {
                $template = $fs->get($intl['item']);
                $h = '';
                if ($template) {
                    $h .= $template['sha1'].$template['dir'].$template['name'];
                } else if(LocalFs::file_exists($intl['item'])) {
                    $h .= LocalFs::file_get_contents($intl['item']);
                } else {
                    // that is bad, it means we have registered files
                    // that does not exists
                    // or that can t be located back.
                    //
                    // you may have forgotten somewhere
                    // $app['intl.fs']->register(__DIR__.'/path/to/templates/', 'ModuleName');
                }
                return $h;
            });
        }
    }
}
