<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use C\Schema\Loader;
use C\FS\Registry;
use C\Watch\WatchedCapsule;

/**
 * Class CapsuleServiceProvider
 *
 * provides support of eloquent database framework,
 * it declares a new schema FS capsule.schema
 * to register database schemas
 *
 * @package C\Provider
 */
class CapsuleServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // set the default database connection credentials
        // it s a root-mysql-localhost
        $app['capsule.connection_defaults'] = array(
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => null,
            'username' => 'root',
            'password' => null,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => null,
            'logging' => false,
        );

        // Set capsule behavior and requirements
        $app['capsule.global'] = true;
        $app['capsule.eloquent'] = true;
        $app['capsule.container'] = $app->share(function() {
            return new Container;
        });

        $app['capsule.dispatcher'] = $app->share(function(Application $app) {
            return new Dispatcher($app['capsule.container']);
        });

        // FS schema cache name
        if (!isset($app['capsule.cache_store_name']))
            $app['capsule.cache_store_name'] = "capsule-store";

        // declare a new schema FS,
        // to register the database schemas of the module
        $app['capsule.schema'] = $app->share(function(Application $app) {
            $store = $app['capsule.cache_store_name'];
            $cache = $app['cache.get']($store);

            $registry = new Registry($store, $cache, [
                'basePath' => $app['project.path']
            ]);
            $registry->restrictWithExtensions([
                'php',
            ]);
            $loader = new Loader($registry);
            $loader->setCapsule($app['capsule']);
            return $loader;
        });

        /*
        if (class_exists('Illuminate\Cache\CacheManager')) {
            $app['capsule.cache_manager'] = $app->share(function() use($app) {
                return new CacheManager($app['capsule.container']);
            });
        }
         */

        // Initialize the capsule provider
        $app['capsule'] = $app->share(function(Application $app) {

            $capsule = new Capsule($app['capsule.container']);

            $capsule->setEventDispatcher($app['capsule.dispatcher']);
            if (isset($app['capsule.cache_manager']) && isset($app['capsule.cache'])) {
//                $capsule->setCacheManager($app['capsule.cache_manager']);
                foreach ($app['capsule.cache'] as $key => $value) {
                    $app['capsule.container']->offsetGet('config')->offsetSet('cache.' . $key, $value);
                }
            }
            if ($app['capsule.global']) {
                $capsule->setAsGlobal();
            }
            if ($app['capsule.eloquent']) {
                $capsule->bootEloquent();
            }

            // you can use capsule.connection as the default,
            // or capsule.connections, to declare multiple connections
            if (!isset($app['capsule.connections'])) {
                $app['capsule.connections'] = array(
                    'default' => (isset($app['capsule.connection']) ? $app['capsule.connection'] : array()),
                );
            }

            // add the connections to the capsule
            // use the defaults of capsule.connection_defaults
            foreach ($app['capsule.connections'] as $connection => $options) {
                $options = array_replace($app['capsule.connection_defaults'], $options);
                $capsule->addConnection($options, $connection);
            }

            // you can use capsule.use_connection to define the connection to use
            // otherwise it fallback to the environment name
            if (!isset($app['capsule.use_connection'])) {
                $app['capsule.use_connection'] = $app['env'];
            }
            // always set a default connection
            if (!isset($app['capsule.connections']['default'])) {
                $capsule->addConnection($app['capsule.connections'][$app['capsule.use_connection']], 'default');
            }

            return $capsule;

        });
    }

    public function boot(Application $app)
    {
        // a new tag computer is registered
        // to bind sql queries to the cache system
        if (isset($app['capsule.eloquent'])) {
            if (isset($app['httpcache.tagger'])) {
                $tagger = $app['httpcache.tagger'];
                $capsule = $app['capsule'];
                /* @var $tagger \C\TagableResource\ResourceTagger */
                $tagger->addTagComputer('sql', function ($sql) use($capsule) {
                    $k = $capsule->getConnection()->select($sql);
                    return $k;
                });
            }
        }

        // enable query logging
        $app->before(function () use($app) {
            $connections = $app['capsule.connections'];
            $capsule = $app['capsule'];
            foreach ($connections as $connection => $options) {
                $options = array_replace($app['capsule.connection_defaults'], $options);
                if ($options['logging']) {
                    $capsule->connection($connection)->enableQueryLog();
                }
            }
        });

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
