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
            'compatible_contents' => []
        ]);

        parent::configureChildOptions($resolver);
        $resolver->setRequired('allowed_modules');
        $resolver->setAllowedTypes('allowed_modules', ['array']);
    }

    protected function buildChildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('modules', ModuleCollectionType::class, [
            'label' => "container.form.modules.label",
            'content_type' => $options['content_type'],
            'allowed_modules' => $options['allowed_modules'],
            'compatible_contents' => [],
        ]);
    }
}