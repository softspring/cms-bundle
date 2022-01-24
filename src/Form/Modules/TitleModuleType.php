<?php

namespace Softspring\CmsBundle\Form\Modules;

use Softspring\PolymorphicFormType\Form\Type\Node\AbstractNodeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TitleModuleType extends AbstractNodeType
{
    public function getBlockPrefix()
    {
        return 'cms_module_title';
    }

    protected function configureChildOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label_format' => 'modules.title.%name%.label',
            'translation_domain' => 'sfs_cms',
            'prototype_button_label' => 'modules.title.prototype_button',
            'prototype_button_attr' => [ 'class' => 'dropdown-item btn btn-light' ],
        ]);
    }

    protected function buildChildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class);
    }
}