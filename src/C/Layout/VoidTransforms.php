<?php
namespace C\Layout;

class VoidTransforms implements TransformsInterface{

    public function __construct(){
    }

    /**
     * @var \C\Layout\Layout
     */
    public $layout;

    public function setLayout (Layout $layout) {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @var \C\Layout\TransformsInterface
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

    public function __call ($a, $b) {
        return $this;
    }
}
