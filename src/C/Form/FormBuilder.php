<?php
namespace C\Form;

use \Symfony\Component\Form\Form;
use C\TagableResource\TagableResourceInterface;
use C\TagableResource\UnwrapableResourceInterface;
use C\TagableResource\TagedResource;

/**
 * Class FormBuilder
 * helps to delay effective
 * form creation to the latest time possible.
 *
 * It also prevents a response to be cached
 * when it contains a block with a form attached to it.
 *
 * @package C\Form
 */
class FormBuilder implements TagableResourceInterface, UnwrapableResourceInterface {

    /**
     * @param Form $form
     * @return FormBuilder
     */
    public static function createView (Form $form) {
        $args = func_get_args();
        array_shift($args);
        return new self($form, $args);
    }

    /**
     * @var Form
     */
    public $form;
    /**
     * @var array
     */
    public $args;

    /**
     * @param Form $form
     * @param array $args
     */
    public function __construct (Form $form, $args=[]) {
        $this->form = $form;
        $this->args = $args;
    }

    /**
     * It ensures form are not taggable
     * because of their csrf protection
     *
     * @param null $asName
     * @return TagedResource
     * @throws \Exception
     */
    public function getTaggedResource ($asName=null) {
        throw new \Exception("not taggable resource");
        $res = new TagedResource();
        return $res;
    }

    /**
     * @return mixed
     */
    public function unwrap () {
        return $this->form->createView(); // this is the reason of this class....!
        // when createView is triggered, the http response is modified to private
        // which prevents cache strategy to be effective
        //      is response cache-able=>false
        // so the createView call is delayed up to unwrap call time,
        // the very last moment before the view requires the data.
    }
}