<?php
namespace C\Misc;
/**
 * Class ArrayHelpers
 * helps to manage
 * an array of helpers.
 *
 * It can prepend / find / remove / replace
 * based on object types.
 *
 * @package C\Misc
 */
class ArrayHelpers extends \ArrayObject {

    /**
     * prepend to this array.
     *
     * @param $some
     */
    public function prepend ($some) {
        $internals = $this->getArrayCopy();
        array_unshift($internals, $some);
        $this->exchangeArray($internals);
    }

    /**
     * merge to this array.
     *
     * @param $some
     */
    public function merge ($some) {
        $internals = $this->getArrayCopy();
        $this->exchangeArray(array_merge($internals, (array) $some));
    }

    /**
     * default values of this array.
     *
     * @param $some
     */
    public function defaults ($some) {
        $internals = $this->getArrayCopy();
        $this->exchangeArray(array_merge((array) $some, $internals));
    }

    /**
     * Find an item given its class type.
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
     * Remove an item given its class type
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
     * Replace an existing item of a similar class type
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
