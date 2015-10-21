<?php

namespace C\HTTP;

use C\TagableResource\TagableResourceInterface;
use C\TagableResource\TagedResource;
use C\TagableResource\UnwrapableResourceInterface;
use Symfony\Component\HttpFoundation\Request;

// @todo rename it to RequestBagsProxy.

/**
 * Class RequestProxy
 * helps to make request bags parameters cache-able via tag resource.
 *
 *
 * @package C\HTTP
 */
class RequestProxy implements TagableResourceInterface, UnwrapableResourceInterface {

    /**
     * @var Request
     */
    public $request;

    /**
     * The repository from which the value should be read.
     * Can be one of _GET _POST _COOKIE _SESSION _HEADER _FILES
     *
     * @var string
     */
    public $repository;
    /**
     * The name of the value to read from the repository.
     *
     * @var string
     */
    public $param;

    /**
     * @param Request $request
     */
    public function __construct ( Request $request ) {
        $this->request = $request;
    }

    /**
     *
     * @return mixed
     */
    public function unwrap() {
        if ($this->repository==='_GET') {
            return $this->request->query->get($this->param, null, true);

        } else if ($this->repository==='_POST') {
            return $this->request->request->get($this->param, null, true);

        } else if ($this->repository==='_COOKIE') {
            return $this->request->cookies->get($this->param, null, true);

        } else if ($this->repository==='_SESSION') {
            return $this->request->getSession()->get($this->param, null, true);

        } else if ($this->repository==='_FILES') {
            return $this->request->files->get($this->param);

        } else if ($this->repository==='_HEADER') {
            return $this->request->headers->get($this->param);

        }
        return false;
    }


    /**
     * @param null $asName
     * @return TagedResource
     * @throws \Exception
     */
    public function getTaggedResource($asName=null) {
        $res = new TagedResource();
        $res->addResource([$this->repository, $this->param], 'request', $asName);
        return $res;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function get ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_GET';
        $proxy->param = $param;
        return $proxy;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function post ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_POST';
        $proxy->param = $param;
        return $proxy;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function file ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_FILES';
        $proxy->param = $param;
        return $proxy;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function cookie ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_COOKIE';
        $proxy->param = $param;
        return $proxy;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function session ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_SESSION';
        $proxy->param = $param;
        return $proxy;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function header ($param) {
        $proxy = new RequestProxy($this->request);
        $proxy->repository = '_HEADER';
        $proxy->param = $param;
        return $proxy;
    }
}