<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Form\Traits\DynamicFormTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFormType extends AbstractType
{
    use DynamicFormTrait;

    public function getBlockPrefix(): string
    {
        return 'dynamic_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $this->configureDynamicFormOptions($resolver);

        $resolver->setDefaults([
            'translation_domain' => 'sfs_cms_modules',
        ]);

        $resolver->setDefault('form_template', null);
        $resolver->setAllowedTypes('form_template', ['null', 'string']);

        $resolver->setDefault('edit_template', null);
        $resolver->setAllowedTypes('edit_template', ['null', 'string']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildDynamicForm($builder, $options);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['form_template'] = $options['form_template'];
        $view->vars['edit_template'] = $options['edit_template'];
    }
}