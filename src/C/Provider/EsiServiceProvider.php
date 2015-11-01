<?php
namespace C\Provider;

use C\Misc\Utils;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EsiServiceProvider
 * provide esi capabilities to the stack.
 *
 * It watches request / response
 * to inject, remove http headers about esi.
 *
 * It then configure the current
 * request type to esi-master or esi-slave,
 * appropriately.
 *
 * It also registers a new templates path
 * Esi:/
 *
 * @package C\Provider
 */
class EsiServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
    }

    public function boot(Application $app)
    {
        // register required esi template
        // to inject in the view
        if (isset($app['layout.fs'])) {
            $app['layout.fs']->register(
                __DIR__.'/../Esi/templates/', 'Esi');
        }

        // implement esi request-response challenge
        $app->before(function(Request $request, Application $app){

            // esi can not occur under ajax
            // due to layout request type matching nature
            // where types exclude each others.
            if (!$request->isXmlHttpRequest()) {

                // use a system secret, known by the front proxy,
                // it should prevent non-authorized entities to triggers such process.
                // it is also used to detect a slave request.
                $secret = $app['esi.secret'];
                if ($request->headers->get("x-esi-secret") && $request->query->has("target")) {
                    $rsecret = $request->headers->get("x-esi-secret");
                    if ($rsecret===$secret) {
                        Utils::stderr("slave found");
                        $app['layout']->requestMatcher->setRequestKind('esi-slave');
                    } else {
                        Utils::stderr("esi secret mismatch");
                        Utils::stderr("request secret was $rsecret");
                    }

                // determine if the request is an esi master type.
                // it injects surrogate control directive
                // in the response object o trigger front proxy esi build
                } else if ($request->headers->has("Surrogate-Capability")) {
                    $app['layout']->requestMatcher->setRequestKind('esi-master');
                    $app['layout']->onLayoutBuildFinish(function ($ev, $layout, $response) {
                        $response->headers->set("Surrogate-Control", "ESI/1.0");
                    });
                }
            }
        });

    }
}
// read more about esi support
//
// https://www.varnish-cache.org/trac/wiki/ESIfeatures
// https://www.varnish-software.com/book/3/Content_Composition.html#edge-side-includes
// https://www.varnish-cache.org/docs/3.0/tutorial/esi.html
// http://blog.lavoie.sl/2013/08/varnish-esi-and-cookies.html
// http://symfony.com/doc/current/cookbook/cache/varnish.html
// http://silex.sensiolabs.org/doc/providers/http_cache.html
// https://github.com/serbanghita/Mobile-Detect
// http://symfony.com/doc/current/cookbook/cache/form_csrf_caching.html
