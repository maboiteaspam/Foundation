<?php
namespace C\Provider;

use C\FS\KnownFs;
use C\FS\KnownFsTagResolver;
use C\FS\LocalFs;
use C\FS\Registry;

use C\Intl\Format\XliffIntlLoader;
use C\Intl\Format\YmlIntlLoader;
use C\Intl\Loader\IntlFileLoader;
use C\Intl\Loader\IntlJitLoader;
use C\Intl\LocaleManager;
use C\Intl\Translator;

use Moust\Silex\Cache\CacheInterface;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use C\Watch\WatchedIntl;

/**
 * Class IntlServiceProvider
 *
 * provides intl capabilities
 * - to load intl from files
 * - support yml / xliff formats
 * - provides translator object
 *
 * It also configures the locale manager
 * just in time
 * given the current request
 * to detect the most appropriate locale
 *
 * @package C\Provider
 */
class IntlServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        LocalFs::$record = $app['debug'];

        // FS intl cache name
        if (!isset($app['intl.cache_store_name']))
            $app['intl.cache_store_name'] = "intl-fs-store";

        // declare a new intl FS,
        // to register the intl of the module
        $app['intl.fs'] = $app->share(function(Application $app) {
            $store = $app['intl.cache_store_name'];
            $cache = $app['cache.get']($store);

            $registry = new Registry($store, $cache, [
                'basePath' => $app['project.path']
            ]);
            $registry->restrictWithExtensions([
                'yml', 'xlf',
            ]);
            return new KnownFs($registry);
        });

        // intl files content cache name
        if (!isset($app['intl-content.cache_store_name']))
            $app['intl-content.cache_store_name'] = "intl-content-store";

        // declare a new cache for intl files content
        $app['intl-content.cache'] = $app->share(function(Application $app) {
            $store = $app['intl-content.cache_store_name'];
            $cache = $app['cache.get']($store);
            return $cache;
        });

        // declare a file loaders
        // to build intl files
        $app['intl.loader'] = $app->share(function(Application $app) {
            $loader = new IntlFileLoader();
            $loader->addLoader(new YmlIntlLoader());
            $loader->addLoader(new XliffIntlLoader());
            return $loader;
        });

        // declare a translations loader
        // to use once the intl files are built
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

        // define a default locale
        $app['locale'] = 'en';
        // add locale challenge decision capabilities
        $app['locale.manager'] = $app->share(function(Application $app) {
            return new LocaleManager();
        });

        // provides the translation capabilities
        $app['translator'] = $app->share(function(Application $app) {
            $translator = new Translator(null, $app['debug']);
            $translator->setJitLoader($app['intl.jitloader']);
            return $translator;
        });

        // configures the fallback for the locale challenge decision
        if (!isset($app['locale_fallbacks'])) $app['locale_fallbacks'] = array('en');
    }

    public function boot(Application $app)
    {
        // register intl FS to the watcher system.
        // it will update the FS
        // it will also trigger intl files build
        // on application start and file change
        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->share(
                $app->extend('watchers.watched',
                    function($watched, Application $app) {
                        $w = new WatchedIntl();
                        $w->setRegistry($app['intl.fs']->registry);
                        $w->setLoader($app['intl.loader']);
                        $w->setJitLoader($app['intl.jitloader']);
                        $w->setName("intl");
                        $watched[] = $w;
                        return $watched;
                    }
                )
            );
        }

        // load and configure translations
        $app->before(function (Request $request) use ($app) {
            // load all translations from cache.
            $app['intl.fs']->registry->loadFromCache();

            // configure the locale manager
            if (isset($app['locale.manager'])) {
                /* @var $localMngr \C\Intl\LocaleManager */
                /* @var $jitLoader \C\Intl\Loader\IntlJitLoader */
                $localMngr = $app['locale.manager'];
                $jitLoader = $app['intl.jitloader'];

                $knownLocales = $jitLoader->fetchWellKnownLocales();
                $localMngr->setFallbackLocales($app['locale_fallbacks']);
                $localMngr->setWellKnownLocales($knownLocales);

                $localMngr->setBestLocalGivenReqLng($request->getPreferredLanguage($knownLocales));
            }
        });

        // a new tag computer is registered
        // to bind assets into the cache system
        if (isset($app['httpcache.tagger'])) {
            /* @var $fs \C\FS\KnownFs */
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $fs = $app['intl.fs'];
            $tagger = $app['httpcache.tagger'];
            $tagger->addTagComputer('intl', new KnownFsTagResolver($fs));
        }
    }
}
// read more about translator here
// http://stackoverflow.com/questions/25482856/basic-use-of-translationserviceprovider-in-silex
