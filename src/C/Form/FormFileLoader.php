<?php
namespace C\Form;

use C\FS\Store;
use Symfony\Component\Form\FormFactory;

class FormFileLoader {

    /**
     * @var Store
     */
    protected $store;


    /**
     * Store is the object responsible
     * to resolve layout file path.
     *
     * @param Store $store
     * @return $this
     */
    public function setStore(Store $store) {
        $this->store = $store;
        return $this;
    }

    /**
     * @param $filePath
     * @return array|mixed
     */
    public function loadFile ($filePath) {
        return $this->store->get($filePath);
    }

    /**
     * @param $filePath
     * @return null|\Symfony\Component\Form\FormBuilderInterface
     */
    public function createFormBuilderFromFile ($filePath) {
        $config = $this->loadFile($filePath);
        if (!$config) return null;
        return $this->createFormBuilder($config);
    }

    /**
     * @var FormFactory
     */
    public $formFactory;

    /**
     * @param FormFactory $factory
     */
    public function setFactory (FormFactory $factory) {
        $this->formFactory = $factory;
    }

    /**
     * @param array $config
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    public function createFormBuilder ($config) {

//        $formId         = isset($config['name'])      ? $config['name']         : 'form';
        $formType       = isset($config['type'])      ? $config['type']         : null;
        $formContent    = isset($config['children'])  ? $config['children']     : [];
        $formAttrs      = isset($config['attr'])      ? $config['attr']         : [];

        $builder = $this->formFactory->createBuilder(
            'form',
            $formType? new $formType : null, ['attr'=>$formAttrs]
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

        return $builder;
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