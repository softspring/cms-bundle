<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class SpacingType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'spacing';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('marginTop', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => [
                '0' => '',
                '1' => 'mt-1',
                '2' => 'mt-2',
                '3' => 'mt-3',
                '4' => 'mt-4',
                '5' => 'mt-5',
            ]
        ]);

        $builder->add('marginEnd', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => [
                '0' => '',
                '1' => 'me-1',
                '2' => 'me-2',
                '3' => 'me-3',
                '4' => 'me-4',
                '5' => 'me-5',
            ]
        ]);

        $builder->add('marginBottom', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => [
                '0' => '',
                '1' => 'mb-1',
                '2' => 'mb-2',
                '3' => 'mb-3',
                '4' => 'mb-4',
                '5' => 'mb-5',
            ]
        ]);

        $builder->add('marginStart', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => [
                '0' => '',
                '1' => 'ms-1',
                '2' => 'ms-2',
                '3' => 'ms-3',
                '4' => 'ms-4',
                '5' => 'ms-5',
            ]
        ]);
        $builder->add('paddingTop', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => [
                '0' => '',
                '1' => 'pt-1',
                '2' => 'pt-2',
                '3' => 'pt-3',
                '4' => 'pt-4',
                '5' => 'pt-5',
            ]
        ]);

        $builder->add('paddingEnd', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => [
                '0' => '',
                '1' => 'pe-1',
                '2' => 'pe-2',
                '3' => 'pe-3',
                '4' => 'pe-4',
                '5' => 'pe-5',
            ]
        ]);

        $builder->add('paddingBottom', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => [
                '0' => '',
                '1' => 'pb-1',
                '2' => 'pb-2',
                '3' => 'pb-3',
                '4' => 'pb-4',
                '5' => 'pb-5',
            ]
        ]);

        $builder->add('paddingStart', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => [
                '0' => '',
                '1' => 'ps-1',
                '2' => 'ps-2',
                '3' => 'ps-3',
                '4' => 'ps-4',
                '5' => 'ps-5',
            ]
        ]);
    }
}