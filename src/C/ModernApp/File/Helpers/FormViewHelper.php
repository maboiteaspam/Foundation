<?php
namespace C\ModernApp\File\Helpers;

use C\Form\FormBuilder;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class FormViewHelper
 *
 * Add new node action 'create_form' to create form object
 * and inject them in the view as default data
 *
 * @package C\ModernApp\File\Helpers
 */
class FormViewHelper extends  AbstractStaticLayoutHelper{

    /**
     * @var FormFactory
     */
    public $formFactory;

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
    public function setUrlGenerator ( UrlGenerator $g) {
        $this->urlGenerator = $g;
    }

    /**
     * Looks for 'create_form' node actions,
     * create a form object and populate it with provided fields,
     * inject the form into the default view data.
     *
     * @param FileTransformsInterface $T
     * @param $blockSubject
     * @param $nodeAction
     * @param $nodeContents
     * @return bool
     */
    public function executeBlockNode (FileTransformsInterface $T, $blockSubject, $nodeAction, $nodeContents) {
        if ($nodeAction==="create_form") {

            $formId         = isset($nodeContents['name']) ? $nodeContents['name'] : 'form';
            $formContent    = isset($nodeContents['children']) ? $nodeContents['children'] : [];

            $builder = $this->formFactory->createBuilder();

            if ($this->defaultMethod) $builder->setMethod($this->defaultMethod);
            if ($this->defaultRoute) $builder->setAction(
                $this->urlGenerator->generate($this->defaultRoute, array_merge([],$this->defaultRouteParameters,[
                    "block"     => $blockSubject,
                    "formId"    => $formId,
                ]))
            );

            foreach ($formContent as $elName=>$elData) {
                $type = isset($elData['type']) ? $elData['type'] : 'text';
                $options = isset($elData['options']) ? $elData['options'] : [];
                $attr = isset($elData['attr']) ? $elData['attr'] : [];
                $options['attr'] = isset($options['attr']) ? array_merge($attr, $options['attr']) : $attr;
                $builder->add($elName, $type, $options);
            }

            $T->setDefaultData($blockSubject, [$formId=>FormBuilder::createView($builder->getForm())]);

            return true;
        }
    }
}
