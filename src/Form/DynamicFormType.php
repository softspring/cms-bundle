<?php

namespace Softspring\CmsBundle\Form;

use Softspring\PolymorphicFormType\Form\Type\Node\AbstractNodeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFormType extends AbstractNodeType
{
    public function getBlockPrefix()
    {
        return 'dynamic_form';
    }

    public function configureChildOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('form_fields');
        $resolver->setAllowedTypes('form_fields', 'array');

        $resolver->setDefault('form_template', null);
        $resolver->setAllowedTypes('form_template', ['null', 'string']);

        $resolver->setDefault('edit_template', null);
        $resolver->setAllowedTypes('edit_template', ['null', 'string']);
    }

    public function buildChildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['form_fields'] as $fieldName => $formField) {
            $fieldOptions = $formField['type_options'];
            // TODO process some options

            $builder->add($fieldName, $this->getFieldType($formField['type']), $fieldOptions);
        }
    }

    public function buildChildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['form_template'] = $options['form_template'];
        $view->vars['edit_template'] = $options['edit_template'];
    }

    protected function getFieldType(string $type): string
    {
        if (!class_exists($type)) {
            $posibleClasses = [
                'Symfony\Component\Form\Extension\Core\Type\\'.ucfirst($type).'Type',
                'Softspring\CmsBundle\Form\Type\\'.ucfirst($type).'Type',
                'App\Form\Type\\'.ucfirst($type).'Type',
                // TODO allow load namespaces from config
            ];

            foreach($posibleClasses as $posibleClass) {
                if (class_exists($posibleClass)) {
                    return $posibleClass;
                }
            }

            throw new \Exception(sprintf('Type not found for "%s"', $type));
        }

        return $type;
    }
}