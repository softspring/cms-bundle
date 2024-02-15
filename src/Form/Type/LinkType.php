<?php

namespace Softspring\CmsBundle\Form\Type;

use Softspring\CmsBundle\Form\DataVisibilityFieldsTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class LinkType extends SymfonyRouteType
{
    use DataVisibilityFieldsTrait;

    public function getBlockPrefix(): string
    {
        return 'link';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', ChoiceType::class, [
            'choice_translation_domain' => 'sfs_cms_types',
            'choices' => [
                'link_type.type.values.route' => 'route',
                'link_type.type.values.anchor' => 'anchor',
                'link_type.type.values.url' => 'url',
            ],
            'choice_attr' => function ($value) {
                return [
                    'data-show-fields' => match ($value) {
                        'route' => 'route',
                        'anchor' => 'anchor',
                        'url' => 'url',
                        default => '',
                    },
                    'data-hide-fields' => match ($value) {
                        'route' => 'anchor,url',
                        'anchor' => 'route,url',
                        'url' => 'route,anchor',
                        default => '',
                    },
                    'data-empty-fields' => match ($value) {
                        'route' => 'anchor,url',
                        'anchor' => 'route,url',
                        'url' => 'route,anchor',
                        default => '',
                    },
                ];
            },
        ]);

        parent::buildForm($builder, $options);

        $builder->add('anchor', TextType::class);
        $builder->add('url', TextType::class);

        $builder->add('target', ChoiceType::class, [
            'choice_translation_domain' => 'sfs_cms_types',
            'choices' => [
                'link_type.target.values._self' => '_self',
                'link_type.target.values._blank' => '_blank',
                'link_type.target.values._parent' => '_parent',
                'link_type.target.values._top' => '_top',
                'link_type.target.values.custom' => 'custom',
            ],
            'choice_attr' => function ($value) {
                return [
                    'data-show-fields' => match ($value) {
                        'custom' => 'custom_target',
                        default => '',
                    },
                    'data-hide-fields' => match ($value) {
                        'custom' => '',
                        default => 'custom_target',
                    },
                    'data-empty-fields' => match ($value) {
                        'custom' => '',
                        default => 'custom_target',
                    },
                ];
            },
        ]);

        $builder->add('custom_target', TextType::class);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);
        $this->transformDataFieldsFinishView($view, 'type');
    }
}
