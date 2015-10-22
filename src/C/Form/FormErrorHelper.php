<?php
namespace C\Form;

use \Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;

/**
 * Class FormErrorHelper
 * helps to transform a submitted form object
 * into a useful serializable array of elements => error messages
 *
 * @see https://github.com/symfony/symfony/issues/7205
 * @package C\Form
 */
class FormErrorHelper
{
    /**
     * Take a form in input and return an error array.
     * it puts global errors into a special globals__ key,
     *      see SF\Form error_bubbling option.
     *
     * For each element of the form with a constraint,
     * it determine a path such
     * [formName]_[elementName]=>[errors]
     *      form_name=>[errors]
     *      form_address=>[errors]
     *      form42_mobile=>[errors]
     *      ect
     * for array values such names[],
     * the array will contain a sub array for each sub element,
     * and so on.
     * Such as acceding the first element is
     *      [formName]_[elementName][0]=>[errors]
     *      form42_mobiles[0]=>Invalid mobile phone !
     *
     * [
     *  globals__=>[ msg1, msg2 ],
     *  elementPath1=>[ msg1, msg2 ],
     *  elementPath2=>[
     *      subElementIndex0=>[ msg1, msg2 ],
     *      subElementIndex1=>[ msg1, msg2 ],
     *  ],
     * ];
     *
     * @param Form $form
     * @return array Associative array of all errors
     */
    public function getFormErrors($form)
    {
        $errors = [];

        if ($form instanceof Form) {
            $name = $form->getName();
            $tempErr = [];
            foreach ($form->getErrors() as $error) {
                $tempErr[] = $error->getMessage();
            }
            if (count($tempErr)>0) $errors['globals__'] = $tempErr;

            foreach ($form->all() as $key => $child) {
                /** @var $child Form */
                $err = $this->getFormChildErrors($child);
                if (count($err)>0) {
                    $errors[$name."_".$child->getPropertyPath()->__toString()] = $err;
                }
            }
        }

        return $errors;
    }

    protected function getFormChildErrors ($form) {
        $errors = [];

        if ($form instanceof Form) {
            foreach ($form->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }

            foreach ($form->all() as $key => $child) {
                /** @var $child Form */
                $err = $this->getFormChildErrors($child);
                if (count($err)>0) {
                    $errors[$child->getPropertyPath()->__toString()] = $err;
                }
            }
        }

        return $errors;
    }
}

