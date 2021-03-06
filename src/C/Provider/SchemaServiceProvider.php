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

        // Schema cache name
        if (!isset($app['schema.cache_store_name']))
            $app['schema.cache_store_name'] = "schema-fs";

        // declare a new schema loader,
        // is has a fs registry to watch for fs.events
        // it provides a Schema\Loader
        $app['schema.fs'] = $app->share(function(Application $app) {
            $store = $app['schema.cache_store_name'];
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
                        $w->setSchemaLoader($app['schema.fs']);
                        $w->setName("schema.fs");
                        $watched[] = $w;
                        return $watched;
                    }
                )
            );
        }

    }
}
