<?php
namespace C\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Class WatcherServiceProvider
 * provide ability to connect your objects
 * to the watcher system provided by c2-bin
 *
 * @package C\Provider
 */
class WatcherServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['watchers.watched'] = $app->share(function() {
            return [];
        });
    }
    public function boot(Application $app)
    {
    }
}
