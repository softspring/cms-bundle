<?php

namespace Softspring\CmsBundle\Form\Module;

use Softspring\CmsBundle\Utils\Hash;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContainerModuleType extends DynamicFormModuleType
{
    public function getBlockPrefix(): string
    {
        return 'container_module';
    }

    public function configureChildOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compatible_contents' => [],
            'module_collection_class' => '',
            'module_row_class' => '',
            'collection_row_attr' => [],
            'allowed_modules' => null,
            'allowed_container_modules' => null,
        ]);

        parent::configureChildOptions($resolver);
        $resolver->setAllowedTypes('allowed_modules', ['array', 'null']); // null means any
        $resolver->setAllowedTypes('allowed_container_modules', ['array', 'null']);
    }

    public function buildChildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildChildForm($builder, $options);

        $builder->add('modules', ModuleCollectionType::class, [
            'label' => 'container.form.modules.label',
            'content_type' => $options['content_type'],
            'allowed_modules' => $options['allowed_modules'],
            'allowed_container_modules' => $options['allowed_container_modules'],
            'module_collection_class' => $options['module_collection_class'],
            'module_row_class' => $options['module_row_class'],
            'compatible_contents' => [],
            // random prototype name to allow multiple levels
            'prototype_name' => '__'.Hash::generate().'__',
            'collection_row_attr' => $options['collection_row_attr'],
            'content' => $options['content'],
        ]);
    }
}
