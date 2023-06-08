<?php

namespace Softspring\CmsBundle\Form\Module;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class DynamicFormModuleType extends AbstractModuleType
{
    public function getBlockPrefix(): string
    {
        return 'dynamic_form_module';
    }

    protected function finishChildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['module_errors'] = $form->getErrors(true, true);
    }
}
