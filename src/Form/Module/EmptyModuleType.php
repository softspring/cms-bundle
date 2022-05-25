<?php

namespace Softspring\CmsBundle\Form\Module;

use Symfony\Component\Form\FormBuilderInterface;

class EmptyModuleType extends AbstractModuleType
{
    public function getBlockPrefix(): string
    {
        return 'empty_module';
    }

    protected function buildChildForm(FormBuilderInterface $builder, array $options)
    {
        // nothing to include
    }
}
