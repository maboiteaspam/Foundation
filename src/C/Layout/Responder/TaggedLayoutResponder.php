<?php
namespace C\Layout\Responder;

use C\Layout\Layout;
use C\TagableResource\ResourceTagger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use C\Misc\Utils;

/**
 * Class TaggedLayoutResponder
 * Renders and responds a tagged layout.
 * A tagged layout expose a TagResource object
 * and can thus be cached.
 *
 * @package C\Layout\Responder
 */
class TaggedLayoutResponder extends LayoutResponder{
    /**
     * @var ResourceTagger
     */
    public $tagger;

    /**
     * @param ResourceTagger $tagger
     */
    public function setTagger(ResourceTagger $tagger){
        $this->tagger = $tagger;
    }

    /**
     * Renders and respond a layout.
     * If the layout is taggable,
     *  it will add extra global resources (device, language, request kind) on the tag
     *  It will then generate the signature of the layout,
     *      in order to set the etag value of the response object.
     *  Finally, if this layout is cache-able, it will set shared-maxage and set the response as public.
     *
     * @param Layout $layout
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Exception
     */
    public function respond(Layout $layout, Request $request, Response $response=null){

        Utils::stderr('rendering layout');

        if (!$response) $response = new Response();

        $layout->emit('controller_build_finish', $response);
        $content = $layout->render();
        $layout->emit('layout_build_finish', $response);

        $TaggedResource = $layout->getTaggedResource();
        if ($TaggedResource===false) {
            Utils::stderr('this layout prevents caching');
            // this layout contains resource which prevent from being cached.
            // we shall not let that happen.
        } else {

            $requestMatcher = $layout->requestMatcher;
            $TaggedResource->addResource($requestMatcher->langPreferred, 'jit-locale');
            $TaggedResource->addResource($requestMatcher->deviceType, 'jit-device');
            $TaggedResource->addResource($requestMatcher->requestKind, 'jit-request-kind');
            $TaggedResource->addResource(null, 'jit-accept');
            $TaggedResource->addResource(($layout->debugEnabled?'with':'without').'-debug', 'jit-debug');

            $etag = $this->tagger->sign($TaggedResource);

            Utils::stderr('response is tagged with '.$etag);
            $response->setProtocolVersion('1.1');
            $response->setETag($etag);

            $response->setPublic();
            $response->setSharedMaxAge(60);
//                    $response->setMaxAge(60*10);
        }

        $response->setContent($content);

        return $response;
    }
}