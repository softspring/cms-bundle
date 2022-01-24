<?php

namespace Softspring\CmsBundle\Form\Modules;

use Softspring\PolymorphicFormType\Form\Type\Node\AbstractNodeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlModuleType extends AbstractNodeType
{
    public function getBlockPrefix()
    {
        return 'cms_module_html';
    }

    protected function configureChildOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label_format' => 'modules.html.%name%.label',
            'translation_domain' => 'sfs_cms',
            'prototype_button_label' => 'modules.html.prototype_button',
            'prototype_button_attr' => [ 'class' => 'dropdown-item btn btn-light' ],
        ]);
    }

    protected function buildChildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code', TextareaType::class);
    }
}