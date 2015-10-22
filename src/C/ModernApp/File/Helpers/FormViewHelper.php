<?php
namespace C\ModernApp\File\Helpers;

use C\Form\FormBuilder;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Generator\UrlGenerator;

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

            $formId         = isset($nodeContents['name'])      ? $nodeContents['name']         : 'form';
            $formType       = isset($nodeContents['type'])      ? $nodeContents['type']         : null;
            $formContent    = isset($nodeContents['children'])  ? $nodeContents['children']     : [];
            $formAttrs      = isset($nodeContents['attr'])      ? $nodeContents['attr']         : [];

            $builder = $this->formFactory->createBuilder('form', $formType? new $formType : null, ['attr'=>$formAttrs]);
            if ($this->defaultMethod)   $builder->setMethod($this->defaultMethod);
            if ($this->defaultRoute)    $builder->setAction(
                $this->urlGenerator->generate($this->defaultRoute, array_merge([],$this->defaultRouteParameters,[
                    "block"     => $blockSubject,
                    "formId"    => $formId,
                ]))
            );

            foreach ($formContent as $elName=>$elData) {

                $type       = isset($elData['type'])        ? $elData['type'] : 'text';
                $options    = isset($elData['options'])     ? $elData['options'] : [];
                $attr       = isset($elData['attr'])        ? $elData['attr'] : [];
                $validation = isset($elData['validation'])  ? $this->createRealConstraintObjects($elData['validation']) : [];

                $options['attr'] = isset($options['attr']) ? array_merge($attr, $options['attr']) : $attr;
                if (count($validation)) $options['constraints'] = $validation;

                $builder->add($elName, $type, $options);
            }

            $T->setDefaultData($blockSubject, [$formId=>FormBuilder::createView($builder->getForm())]);

            return true;
        }
    }

    /**
     * It transforms an array describing constraint validation
     * and their options into an array of constraint objects.
     *
     * [
     *  'ConstraintClass'=>[
     *      // constraint options
     *  ]
     * ]
     *
     * When constraint class contains a backslash,
     * it considers it as FQClassName.
     * Otherwise, it will prepend the default symfony constraint namespace,
     *  \Symfony\Component\Validator\Constraints\...
     * before attempting to create the constraint object.
     *
     * @param $constraints
     * @return array
     */
    protected function createRealConstraintObjects ($constraints) {
        $ret = [];
        foreach ($constraints as $constraint) {
            $constraintClass = array_keys($constraint)[0];
            $options = $constraint[$constraintClass];
            if (strpos($constraintClass, '\\')===false) {
                $constraintClass = "\\Symfony\\Component\\Validator\\Constraints\\$constraintClass";
            }
            $ret[] = new $constraintClass($options);
        }
        return $ret;
    }
}
