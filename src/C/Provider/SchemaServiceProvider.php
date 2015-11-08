<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use C\Schema\Loader;
use C\FS\Registry;
use C\Watch\WatchedCapsule;

/**
 * Class SchemaServiceProvider
 * declares a new schema FS schema.fs
 * to register database schemas
 *
 * @package C\Provider
 */
class SchemaServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // declare a new schema FS,
        // to register the database schemas of the module
        $app['schema.fs'] = $app->share(function(Application $app) {
            $store = $app['capsule.cache_store_name'];
            $cache = $app['cache.get']($store);

            $registry = new Registry($store, $cache, [
                'basePath' => $app['project.path']
            ]);
            $registry->restrictWithExtensions([
                'php',
            ]);
            $loader = new Loader($registry);
            return $loader;
        });
        // this is an alias for b-compatibility
        // @todo refactor until this alias can disappear.
        $app['capsule.schema'] = $app->share(function(Application $app) {
            return $app['schema.fs'];
        });

    }

    public function boot(Application $app)
    {
        // register schema FS to the watcher system.
        // it will update ths FS
        // it will also trigger database build on application start
        if (isset($app['watchers.watched'])) {
            $app['watchers.watched'] = $app->share(
                $app->extend('watchers.watched',
                    function($watched, Application $app) {
                        $w = new WatchedCapsule();
                        $w->setSchemaLoader($app['capsule.schema']);
                        $w->setName("capsule.schema");
                        $watched[] = $w;
                        return $watched;
                    }
                )
            );
        }

    }
}
