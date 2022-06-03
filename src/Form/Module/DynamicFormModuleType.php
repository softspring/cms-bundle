<?php

namespace Softspring\CmsBundle\Form\Module;

use Softspring\Component\DynamicFormType\Form\DynamicFormTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFormModuleType extends AbstractModuleType
{
    use DynamicFormTrait;

    public function getBlockPrefix(): string
    {
        return 'dynamic_form_module';
    }

    public function configureChildOptions(OptionsResolver $resolver)
    {
        parent::configureChildOptions($resolver);
        $this->configureDynamicFormOptions($resolver);
    }

    public function buildChildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildDynamicForm($builder, $options);
    }

    protected function getFormClasses(string $type): array
    {
        return [
            'App\Form\Type\\'.ucfirst($type).'Type',
            'Softspring\CmsBundle\Form\Type\\'.ucfirst($type).'Type',
            'Symfony\Component\Form\Extension\Core\Type\\'.ucfirst($type).'Type',
        ];
    }
}
