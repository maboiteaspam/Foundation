<?php
namespace C\ModernApp\File\Helpers;

use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;
use C\ModernApp\jQuery\Transforms as jQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;

class jQueryHelper extends  AbstractStaticLayoutHelper{

    /**
     * @var UrlGenerator
     */
    protected $generator;
    /**
     * @var Request
     */
    protected $request;

    public function setGenerator (UrlGenerator $generator) {
        $this->generator = $generator;
    }

    public function setRequest (Request $request) {
        $this->request = $request;
    }

    /**
     * Provide ajaxify structure change
     *
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContents
     * @return mixed
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        if ($nodeAction==="ajaxify") {
            $generator = $this->generator;
            $request = $this->request;

            $T->then(function() use($T, $nodeContents, $generator, $request){
                $requestRoute = [
                    'route'=>$this->request->get('_route'),
                    'params'=>$this->request->get('_route_params'),
                ];
                $route = array_merge($requestRoute, isset($nodeContents['route']) ? $nodeContents['route'] : []);
                jQuery::transform()
                    ->setLayout($T->getLayout())
                    ->ajaxify($nodeContents['id'], [
                        'url'   => $generator->generate($route['route'], $route['params']),
                    ]);
            });
            return $T;
        }
    }

    /**
     * Provide a new block action to inject jquery in your view.
     *
     * @param FileTransformsInterface $T
     * @param $blockTarget
     * @param $nodeAction
     * @param $nodeContents
     * @return bool
     */
    public function executeBlockNode (FileTransformsInterface $T, $blockTarget, $nodeAction, $nodeContents) {
        if ($nodeAction==="inject_jquery") {
            if (is_string($nodeContents)) {
                $nodeContents = [
                    'version'   => $nodeContents,
                    'target'    => "page_footer_js",
                ];
            }
            $version = $nodeContents['version'];
            $nodeContents = array_merge([
                'jquery'    => "jQuery:/jquery-{$version}.min.js",
                'target'    => $nodeContents['target'],
            ], $nodeContents);
            jQuery::transform()
                ->setLayout($T->getLayout())
                ->inject($nodeContents);
            return true;

        } else if ($nodeAction==="dom_prepend_to") {

        } else if ($nodeAction==="dom_append_to") {

        } else if ($nodeAction==="dom_prepend_with") {

        } else if ($nodeAction==="dom_append_with") {

        } else if ($nodeAction==="dom_remove") {

        }
    }
}
