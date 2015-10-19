<?php
namespace C\Misc;

class ArrayHelpers extends \ArrayObject {

    public function prepend ($some) {
        $internals = $this->getArrayCopy();
        array_unshift($internals, $some);
        $this->exchangeArray($internals);
    }

    /**
     * @param $some mixed
     * @return bool
     */
    public function isValid ($some) {
        return in_array($this->interface, class_implements($some));
    }

    /**
     * Find an item given its type.
     * @param $some string
     * @return mixed|null
     */
    public function find ($some) {
        foreach($this as $o){
            if (is_a($o, $some)) {
                return $o;
            }
        }
        foreach($this as $o){
            if (strpos(get_class($o), $some)!==false) {
                return $o;
            }
        }
        return null;
    }

    /**
     * Remove an item given its type
     * @param $some string
     * @return bool
     */
    public function remove ($some) {
        $found = $this->find($some);
        if ($found!==null) {
            $internals = $this->getArrayCopy();
            array_splice($internals, $i, 1);
            $this->exchangeArray($internals);
        }
        return $found!==null;
    }

    /**
     * Replace an existing item of a similar type or append to the array.
     * @param $some string
     * @return bool
     */
    public function replace ($some) {
        $found = $this->find($some);
        if ($found!==null) {
            $internals = $this->getArrayCopy();
            array_splice($internals, $i, 1);
            $this->exchangeArray($internals);
        }
        return $found!==null;
    }
}
