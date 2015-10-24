<?php
namespace C\ModernApp\File\Helpers;

use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;
use C\Esi\Transforms as Esi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;

class EsiHelper extends  AbstractStaticLayoutHelper{

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
     * Provide esify structure change
     *
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContents
     * @return mixed
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        if ($nodeAction==="esify") {
            $generator = $this->generator;
            $requestRoute = [
                'route'=>$this->request->get('_route'),
                'params'=>$this->request->get('_route_params'),
            ];
            $route = array_merge($requestRoute, isset($nodeContents['route']) ? $nodeContents['route'] : []);

            Esi::transform()
                ->setLayout($T->getLayout())
                ->esify($nodeContents['id'], [
                    'url'   => $generator->generate($route['name'], $route['params']),
                ]);

            return $T;

        }
    }
}
