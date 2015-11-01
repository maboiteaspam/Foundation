<?php
namespace C\TagableResource;

/**
 * Class Ghoster
 * provide ability to ghost an object call
 * to later unwrap or tag it.
 *
 * @package C\TagableResource
 */
class Ghoster implements TagableResourceInterface, UnwrapableResourceInterface {

    /**
     * @var mixed
     */
    public $ghosted;
    /**
     * @var array
     */
    public $methods;

    public function __construct ($ghosted) {
        $this->ghosted = $ghosted;
    }

    /**
     * @param $method
     * @param $args
     * @return $this
     */
    public function __call ($method, $args) {
        $this->methods[] = [$method, $args,];
        return $this;
    }

    /**
     * @param $methods
     * @return $this
     */
    public function setMethods ($methods) {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return mixed
     */
    public function unwrap() {
        $ghosted = $this->ghosted;
        foreach ($this->methods as $method) {
            $method_name = $method[0];
            $method_args = $method[1];
            foreach( $method_args as $index=>$arg) {
                if ($arg instanceof UnwrapableResourceInterface) {
                    $method_args[$index] = $arg->unwrap();
                }
            }
            $ghosted = call_user_func_array([$ghosted, $method_name], $method_args);
        }
        return $ghosted;
    }

    /**
     * @param null $asName
     * @return TagedResource|mixed
     * @throws \Exception
     */
    public function getTaggedResource($asName=null) {
        $res = new TagedResource();

        $res->addResource(
            [get_class($this->ghosted), $this->methods],
            'ghosted', $asName);

        foreach ($this->methods as $method) {
            $method_args = $method[1];
            foreach( $method_args as $index=>$arg) {
                if ($arg instanceof TagableResourceInterface) {
                    $res->addTaggedResource($arg->getTaggedResource(), $asName);
                }
            }
        }
        return $res;
    }

}