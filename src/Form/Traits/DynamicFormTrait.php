<?php

namespace Softspring\CmsBundle\Form\Traits;

use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

trait DynamicFormTrait
{
    public function configureDynamicFormOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('form_fields');
        $resolver->setAllowedTypes('form_fields', 'array');
        $resolver->setDefault('form_fields', []);
    }

    public function buildDynamicForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['form_fields'] as $fieldName => $formField) {
            $builder->add($fieldName, $this->getFieldType($formField['type']??'text'), $formField['type_options'] ?? []);
        }
    }

    protected function getFieldType(string $type): string
    {
        if (class_exists($type)) {
            return $type;
        }

        $posibleClasses = [
            'App\Form\Type\\'.ucfirst($type).'Type',
            'Softspring\CmsBundle\Form\Type\\'.ucfirst($type).'Type',
            'Symfony\Component\Form\Extension\Core\Type\\'.ucfirst($type).'Type',
        ];

        foreach($posibleClasses as $posibleClass) {
            if (class_exists($posibleClass)) {
                return $posibleClass;
            }
        }

        throw new InvalidConfigurationException(sprintf('Type not found for "%s" in dynamic form', $type));
    }
}