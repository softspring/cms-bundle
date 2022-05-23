<?php

namespace Softspring\CmsBundle\Form\Module;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContainerModuleType extends AbstractModuleType
{
    public function getBlockPrefix(): string
    {
        return 'container_module';
    }

    public function configureChildOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compatible_contents' => [],
            'module_collection_class' => '',
            'module_row_class' => '',
        ]);

        parent::configureChildOptions($resolver);
        $resolver->setRequired('allowed_modules');
        $resolver->setDefault('allowed_modules', null);
        $resolver->setAllowedTypes('allowed_modules', ['array', 'null']); // null means any
        $resolver->setRequired('allowed_container_modules');
        $resolver->setAllowedTypes('allowed_container_modules', ['array']);
    }

    protected function buildChildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('modules', ModuleCollectionType::class, [
            'label' => "container.form.modules.label",
            'content_type' => $options['content_type'],
            'allowed_modules' => $options['allowed_modules'],
            'allowed_container_modules' => $options['allowed_container_modules'],
            'module_collection_class' => $options['module_collection_class'],
            'module_row_class' => $options['module_row_class'],
            'compatible_contents' => [],
            // random prototype name to allow multiple levels
            'prototype_name' => '__'.substr(sha1(rand(0,10000000000)), rand(0,10), 8).'__',
        ]);
    }
}