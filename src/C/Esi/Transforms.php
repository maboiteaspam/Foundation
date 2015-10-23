<?php
namespace C\Esi;

use C\Layout\Transforms\Transforms as base;

/**
 * Class Transforms
 * ESI renderer and layout modifier
 *
 * See varnish documentation for more help and information.
 *
 * @package C\Esi
 */
// https://www.varnish-cache.org/trac/wiki/ESIfeatures
// https://www.varnish-software.com/book/3/Content_Composition.html#edge-side-includes
// https://www.varnish-cache.org/docs/3.0/tutorial/esi.html
// http://blog.lavoie.sl/2013/08/varnish-esi-and-cookies.html
// http://symfony.com/doc/current/cookbook/cache/varnish.html
// http://silex.sensiolabs.org/doc/providers/http_cache.html
// https://github.com/serbanghita/Mobile-Detect
// http://symfony.com/doc/current/cookbook/cache/form_csrf_caching.html
class Transforms extends base{

    /**
     * @return Transforms
     */
    public static function transform(){
        return new self();
    }

    /**
     * Esi-fy the given block id.
     * Anything following this block
     * is rendered when the request is not esi-master.
     *
     * @param $target
     * @param array $options
     * @return $this|\C\Layout\Transforms\VoidTransforms
     */
    public function esify($target, $options=[]){
        $options = array_merge(['url'=>'',], $options);
        return $this
            ->forRequest('esi-master')
            ->then(function (Transforms $transform) use ($target, $options) {
                $transform->clearBlock($target
                )->setTemplate($target,
                    'Esi:/esified-block.php'
                )->updateData($target, [
                    'url'   => $options['url'],
                    'target'=> $target,
                ]);
            })
            ->forRequest('esi-slave')
            ->then(function (Transforms $transform) use($target) {
                if ($_GET['target']===$target) {
                    $transform->getLayout()->block = $target;
                }
            })->forRequest('!esi-master');
    }

}
