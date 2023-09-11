<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\Component\DynamicFormType\Form\DynamicFormType as BaseDynamicFormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFormType extends BaseDynamicFormType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_modules',
        ]);

        $resolver->setDefault('form_template', null);
        $resolver->setAllowedTypes('form_template', ['null', 'string']);

        $resolver->setDefault('edit_template', null);
        $resolver->setAllowedTypes('edit_template', ['null', 'string']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['form_template'] = $options['form_template'];
        $view->vars['edit_template'] = $options['edit_template'];
    }
}
