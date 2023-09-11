<?php

namespace Softspring\CmsBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DefaultValueExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('default_value', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (empty($options['default_value'])) {
            return;
        }

        // TODO this only works on required fields, on other fields it wont allow them to be empty
        $builder->addModelTransformer(new CallbackTransformer(function ($value) use ($options) {
            return $value ?? $options['default_value'];
        }, function ($value) {
            return $value;
        }));
    }
}
