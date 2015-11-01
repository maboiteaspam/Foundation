<?php

namespace C\HTTP;

use C\TagableResource\TagResolverInterface;
use Symfony\Component\HttpFoundation\Request;


class RequestTagResolver implements TagResolverInterface {

    /**
     * @var Request
     */
    public $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * @param $value
     * @return array|mixed|string
     * @throws \Exception
     */
    public function resolve ($value) {
        $request = $this->request;
        if ($value[0]==='_GET') {
            return $request->query->get($value[1], null, true);

        } else if ($value[0]==='_POST') {
            return $request->request->get($value[1], null, true);

        } else if ($value[0]==='_COOKIE') {
            return $request->cookies->get($value[1], null, true);

        } else if ($value[0]==='_SESSION') {
            return $request->getSession()->get($value[1], null, true);

        } else if ($value[0]==='_FILES') {
            return $request->files->get($value[1]);

        } else if ($value[0]==='_HEADER') {
            return $request->headers->get($value[1]);

        }
        throw new \Exception("missing computer for repository {$value[0]}");
    }

}