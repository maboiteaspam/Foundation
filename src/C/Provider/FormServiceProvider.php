<?php
namespace C\Provider;

use C\FS\KnownFs;
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
                $fs->register($path, 'Validator');
            }
        }

        if (isset($app['assets.fs'])) {
            $app['assets.fs']->register(__DIR__.'/../Form/assets/', 'Form');
        }
    }
}
