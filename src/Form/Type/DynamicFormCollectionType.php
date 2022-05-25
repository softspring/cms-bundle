<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFormCollectionType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'dynamic_form_collection';
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'entry_type' => DynamicFormType::class,
            'prototype_initial_elements' => 1,
        ]);

        $resolver->setAllowedTypes('prototype_initial_elements', ['int']);
    }
}
