<?php
namespace C\ModernApp\File\Helpers;

use C\Form\FormBuilder;
use C\Layout\Transforms;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;
use Symfony\Component\Form\FormFactory;

class FormViewHelper extends  AbstractStaticLayoutHelper{

    /**
     * @var FormFactory
     */
    public $formFactory;

    public function setFactory ( FormFactory $factory) {
        $this->formFactory = $factory;
    }

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
                ->sefDefaultData($blockSubject, [$formId=>FormBuilder::createView($form)]);
            return true;
        }
    }
}
