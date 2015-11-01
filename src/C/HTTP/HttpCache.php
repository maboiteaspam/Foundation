<?php

namespace C\HTTP;

use C\Misc\Utils;
use C\TagableResource\ResourceTagger;
use C\TagableResource\TagedResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HttpCache
 * is a facade helper to speak http cache
 * it can tells
 * - if a request or a response is cache-able
 * - it can find resources given etags or uri
 * - it can tell if a resource if fresh
 * - it can transform a resource into a response from the cache
 * - it can transform a response form the controller into a cache-able resource
 *
 * @package C\HTTP
 */
class HttpCache {

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Store
     */
    public $store;

    /**
     * @var ResourceTagger
     */
    public $tagger;

    /**
     * @param Request $request
     */
    public function setRequest (Request $request) {
        $this->request = $request;
    }

    /**
     * @param Store $store
     */
    public function setStore (Store $store) {
        $this->store = $store;
    }

    /**
     * @param ResourceTagger $tagger
     */
    public function setTagger (ResourceTagger $tagger) {
        $this->tagger = $tagger;
    }

    /**
     * @return array
     */
    public function getProperEtags () {
        $etags = $this->request->getETags();
        foreach ($etags as $i => $etag) {
            if (!in_array($etag, ['*'])) {
                $etags[$i] = str_replace(['"',"'"], '', $etag);
            }
        }
        return $etags;
    }

    /**
     * @param Response $response
     * @return bool
     */
    public function isCacheAbleResponse (Response $response) {
        return $response->isCacheable()
        && !$response->getStatusCode()!==304
        && !$response->headers->has("X-CACHED");
    }

    /**
     * @param $response
     * @return bool
     */
    public function isCacheAble ($response) {
        return $this->request->isMethodSafe()
        && (!$response || $response && $this->isCacheAbleResponse($response));
    }

    /**
     * @param $etag
     * @return mixed
     */
    public function getResourceByEtag ($etag) {
        return $this->store->getResource($etag);
    }

    /**
     * @param $uri
     * @return mixed
     */
    public function getResourceByUri ($uri) {
        // @todo this must check vary by headers (lang / UA)
        return $this->store->getEtag($uri);
    }

    /**
     * @param TagedResource $res
     * @return mixed
     */
    public function isFreshResource (TagedResource $res) {
        return $this->tagger->isFresh($res);
    }

    /**
     * @param $checkFreshness
     * @return bool|Response
     */
    public function getFirstResponseMatchingByEtag ($checkFreshness) {
        $etags = $this->getProperEtags();
        $request = $this->request;
        foreach ($etags as $etag) {
            Utils::stderr("check etag {$etag} for uri {$request->getUri()}");
            $res = $this->getResourceByEtag($etag);
            if ($res!==false) {
                $fresh = !$checkFreshness ||
                    $checkFreshness && $this->isFreshResource($res);
                if ($fresh) {
                    return $this->createCachedResponse($etag);
                }
            }
        }
        return false;
    }

    /**
     * @param $checkFreshness
     * @return bool|Response
     */
    public function getResponseMatchingByUri ($checkFreshness) {
        $request = $this->request;
        $etag = $this->getResourceByUri($request->getUri());
        if ($etag) {
//            Utils::stderr("check etag {$etag} for uri {$request->getUri()}");
            $res = $this->getResourceByEtag($etag);
            if ($res!==false) {
                $fresh = !$checkFreshness ||
                    $checkFreshness && $this->isFreshResource($res);
                if ($fresh) {
                    return $this->createCachedResponse($etag);
                }
            }
        }
        return false;
    }

    /**
     * @param $id
     * @return Response
     */
    public function createCachedResponse ($id) {
        $content = $this->store->getContent($id);
        $body = $content['body'];
        $response = new Response();
        $response->headers->replace($content['headers']);
        $response->setProtocolVersion('1.1');
        $response->setContent($body);
        $response->headers->set("X-CACHED", "true");
        return $response;
    }

    /**
     * @param Response $response
     * @param TagedResource $resource
     */
    public function storeResponseToCache (Response $response, TagedResource $resource) {
        $headers = $response->headers->all();
        // those are headers to save into cache.
        // later when the cache is served, they are re injected.
        $headers = Utils::arrayPick($headers, [
            'cache-control', 'etag', 'last-modified', 'expires',
            'date',
            'Surrogate-Capability',
        ]);
        $this->store->store( $resource,
            $this->request->getUri(), [
            'headers'   => $headers,
            'body'      => $response->getContent()
        ]);
    }
}
