<?php
namespace C\Layout;

/**
 * Interface TransformsInterface
 *
 * Transforms are responsible to apply transformation on layout and their block.
 *
 * @package C\Layout
 */
interface TransformsInterface{

    /**
     * @param Layout $layout
     * @return mixed
     */
    public function setLayout (Layout $layout);

    /**
     * @return Layout
     */
    public function getLayout ();

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function setDefaultData($id, $data=[]);

    // @todo put more definition of Transform methods here
}
