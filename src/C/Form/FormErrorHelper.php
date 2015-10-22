<?php
namespace C\Form;

use \Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;

class FormErrorHelper
{
    /**
     * @see https://github.com/symfony/symfony/issues/7205
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

    protected function getFormChildErrors ($form, $name="") {
        $errors = [];

        if ($form instanceof Form) {
            foreach ($form->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }

            foreach ($form->all() as $key => $child) {
                /** @var $child Form */
                $err = $this->getFormChildErrors($child);
                if (count($err)>0) {
                    $errors[$name.$child->getPropertyPath()->__toString()] = $err;
                }
            }
        }

        return $errors;
    }
}

