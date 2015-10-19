<?php
namespace C\ModernApp\File\Helpers;

use C\Form\FormBuilder;
use C\Layout\Transforms;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;
use Symfony\Component\Form\FormFactory;

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

            $form           = $this->formFactory->createBuilder()->getForm();
            $formId         = isset($nodeContents['name']) ? $nodeContents['name'] : 'form';
            $formContent    = isset($nodeContents['children']) ? $nodeContents['children'] : [];

            foreach ($formContent as $elName=>$elData) {
                $type = isset($elData['type']) ? $elData['type'] : 'text';
                $options = isset($elData['options']) ? $elData['options'] : [];
                $attr = isset($elData['attr']) ? $elData['attr'] : [];
                $options['attr'] = isset($options['attr']) ? array_merge($attr, $options['attr']) : $attr;
                $form->add($elName, $type, $options);
            }

            Transforms::transform()
                ->setLayout($T->getLayout())
                ->setDefaultData($blockSubject, [$formId=>FormBuilder::createView($form)]);
            return true;
        }
    }
}
