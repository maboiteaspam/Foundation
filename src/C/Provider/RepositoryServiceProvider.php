<?php
namespace C\Provider;

use C\Repository\RepositoryGhoster;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Class RepositoryServiceProvider
 * provides a tag computer for ghosted service calls
 *
 * @package C\Provider
 */
class RepositoryServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
    }

    public function boot(Application $app)
    {
        if (isset($app['httpcache.tagger'])) {
            $tagger = $app['httpcache.tagger'];
            // this will resolve a repository data proxy.
            // the repository data records methods calls,
            // this resolver executes those methods calls on-delayed-demand.
            /* @var $tagger \C\TagableResource\ResourceTagger */
            $tagger->addTagComputer('repository', function ($data) use($app) {
                $repositoryName = $data[0];
                $ghoster = new RepositoryGhoster($app[$repositoryName]);
                return $ghoster->setMethods($data[1])->unwrap();
            });

            $tagger->addTagComputer('ghosted', function ($data) use($app) {
                $instance = $data[0];
                $ghoster = new RepositoryGhoster($instance);
                return $ghoster->setMethods($data[1])->unwrap();
            });
        }
    }
}