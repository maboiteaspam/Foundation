<?php
namespace C\Layout\Transforms;

use C\Layout\Layout;

/**
 * Class VoidTransforms
 * will cancel all attempts to modify the layout.
 *
 * @package C\Layout
 */
class VoidTransforms implements TransformsInterface{
    /**
     * @var \C\Layout\Layout
     */
    public $layout;

    public function setLayout (Layout $layout) {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @var \C\Layout\Transforms\TransformsInterface
     */
    public $innerTransform;

    public function setInnerTransform (TransformsInterface $T) {
        $this->innerTransform = $T;
        return $this;
    }

    /**
     * @return Layout
     */
    public function getLayout () {
        return $this->layout;
    }
    public function forDevice ($device) {
        if ($this->layout->requestMatcher->isDevice($device)) {
            return $this->innerTransform;
        }
        return $this;
    }
    public function forRequest ($kind) {
        if ($this->layout->requestMatcher->isRequestKind($kind)) {
            return $this->innerTransform;
        }
        return $this;
    }
    public function forLang ($lang) {
        if ($this->layout->requestMatcher->isLang($lang)) {
            return $this->innerTransform;
        }
        return $this;
    }

    /**
     * Magic method which cancel everything else.
     *
     * @param $a
     * @param $b
     * @return $this
     */
    public function __call ($a, $b) {
        return $this;
    }

    /**
     * voided method.
     *
     * @param $id
     * @param array $data
     * @return $this
     */
    public function setDefaultData ($id, $data=[]) {
        return $this;
    }
}
