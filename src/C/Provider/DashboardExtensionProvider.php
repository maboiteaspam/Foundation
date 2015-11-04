<?php
namespace C\Provider;

use C\Misc\ArrayHelpers;
use C\ModernApp\DashboardExtension\LayoutSerializer;
use Silex\Application;
use Silex\ServiceProviderInterface;
use C\ModernApp\DashboardExtension\Transforms;

/**
 * Class DashboardExtensionProvider
 * Provides extensions to the
 * dashboard displayed.
 *
 * It also declare new assets
 * and layouts module path
 * DashboardExtension:/
 *
 * @package C\Provider
 */
class DashboardExtensionProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
    }

    public function boot(Application $app)
    {
        // register the new extensions
        // to the dashboard as a layout transform
        if (isset($app['modern.dashboard.extensions'])) {
            $app['modern.dashboard.extensions'] = $app->share(
                $app->extend('modern.dashboard.extensions',
                    function ($extensions) use($app) {
                        $serializer = new LayoutSerializer();
                        $serializer->setApp($app);
                        if(isset($app["assets.fs"])) $serializer->setAssetsFS($app["assets.fs"]);
                        if(isset($app["layout.fs"])) $serializer->setLayoutFS($app["layout.fs"]);
                        if(isset($app["modern.fs"])) $serializer->setModernFS($app["modern.fs"]);

                        $extensions[] = Transforms::transform()
                            ->setLayoutSerializer($serializer)
                            ->setLayout($app['layout']);
                        return $extensions;
                    }
                )
            );
        }

        // register a new layout file helper
        //  structure:
        //      show_dashboard:
        //          - ext1
        //          - ext2
        if (isset($app['modern.layout.helpers'])) {
            $app['modern.layout.helpers'] = $app->extend('modern.layout.helpers',
                $app->share(
                    function (ArrayHelpers $helpers) use($app) {
                        $helper = new \C\ModernApp\Dashboard\DashboardLayoutFileHelper();
                        $helper->setExtensions($app['modern.dashboard.extensions']);
                        $helpers->append($helper);
                        return $helpers;
                    }
                )
            );
        }


        // provide the assets and layouts
        // of dashboard extensions
        if (isset($app['assets.fs'])) {
            $app['assets.fs']->register(
                __DIR__.'/../ModernApp/DashboardExtension/assets/',
                'DashboardExtension');
        }
        if (isset($app['layout.fs'])) {
            $app['layout.fs']->register(
                __DIR__.'/../ModernApp/DashboardExtension/templates/',
                'DashboardExtension');
        }
    }
}
