<?php
namespace C\ModernApp\File\Helpers;

use C\Form\FormBuilder;
use C\Form\FormFileLoader;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class FormViewHelper
 *
 * Add new node action 'create_form' to create form objects
 * and inject them in the view as default data.
 * Because they are injected as default data
 * it gives capability to backend controller to rewrite it.
 *
 * @package C\ModernApp\File\Helpers
 */
class FormViewHelper extends  AbstractStaticLayoutHelper{

    /**
     * @var FormFactory
     */
    public $formFactory;

    /**
     * @param FormFactory $factory
     */
    public function setFactory ( FormFactory $factory) {
        $this->formFactory = $factory;
    }

    /**
     * The route name to submit forms.
     *
     * @var string
     */
    protected $defaultRoute = null;
    /**
     * The method type for form submission
     * @var string
     */
    protected $defaultMethod = null;
    /**
     * An array of extra parameters injected
     * in the url generator.
     *
     * @var array
     */
    protected $defaultRouteParameters = null;

    /**
     * Set a default route to submit form.
     *
     * @param $name
     */
    public function setSubmitRoute($name) {
        $this->defaultRoute = $name;
    }

    /**
     * Add extra route parameters.
     *
     * @param array $parameters
     */
    public function setRouteParameters(array $parameters) {
        $this->defaultRouteParameters = $parameters;
    }

    /**
     * Set a default method for each created form.
     * @param $method
     */
    public function setDefaultMethod ($method) {
        $this->defaultMethod = $method;
    }

    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @param UrlGenerator $g
     */
    public function setUrlGenerator ( UrlGenerator $g) {
        $this->urlGenerator = $g;
    }

    /**
     * @var FormFileLoader
     */
    protected $formLoader;

    /**
     * @param FormFileLoader $formLoader
     */
    public function setFormLoader ( FormFileLoader $formLoader) {
        $this->formLoader = $formLoader;
    }

    /**
     * Looks for 'create_form' node actions,
     * create a form object and populate it with provided fields,
     * inject the form into the default view data.
     *
     *  structure:
     *      block_id:
     *          create_form:
     *              name: FormName
     *              attr: {some: "attr"}
     *              children:
     *                  element_name:
     *                      type: email|text
     *                      options: {label: "Your email", data: "some"}
     *                      validation:
     *                          - NotBlank: ~
     *                          - Email:
     *                              pattern: /valid email/
     *
     * @param FileTransformsInterface $T
     * @param $blockSubject
     * @param $nodeAction
     * @param $nodeContents
     * @return bool
     */
    public function executeBlockNode (FileTransformsInterface $T, $blockSubject, $nodeAction, $nodeContents) {
        if ($nodeAction==="create_form") {

            $formId = isset($nodeContents['name']) ? $nodeContents['name'] : 'form';

            $builder = $this->formLoader->createFormBuilder($nodeContents);
            if ($this->defaultMethod) $builder->setMethod($this->defaultMethod);
            if ($this->defaultRoute) $builder->setAction(
                $this->urlGenerator->generate($this->defaultRoute, array_merge([], $this->defaultRouteParameters, [
                    "block" => $blockSubject,
                    "formId" => $formId,
                ]))
            );

            $T->setDefaultData($blockSubject, [$formId => FormBuilder::createView($builder->getForm())]);

            return true;

        } else if ($nodeAction==="import_form") {

            $formFile   = isset($nodeContents['from']) ? $nodeContents['from'] : '';
            $formId     = isset($nodeContents['as']) ? $nodeContents['as'] : 'form';

            $builder = $this->formLoader->createFormBuilderFromFile($formFile);
            if ($this->defaultMethod)   $builder->setMethod($this->defaultMethod);
            if ($this->defaultRoute)    $builder->setAction(
                $this->urlGenerator->generate($this->defaultRoute, array_merge([],$this->defaultRouteParameters,[
                    "block"     => $blockSubject,
                    "formId"    => $formId,
                ]))
            );

            $T->setDefaultData($blockSubject, [$formId=>FormBuilder::createView($builder->getForm())]);

            return true;
        }
    }
}
