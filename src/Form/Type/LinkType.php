<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class LinkType extends SymfonyRouteType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', ChoiceType::class, [
            'choices' => [
                'anchor' => 'anchor',
                'route' => 'route',
                'url' => 'url',
            ],
        ]);

        parent::buildForm($builder, $options);

        $builder->add('anchor', TextType::class);
        $builder->add('url', TextType::class);

        $builder->add('target', ChoiceType::class, [
            'choices' => [
                '_self' => '_self',
                '_blank' => '_blank',
                '_parent' => '_parent',
                '_top' => '_top',
                'custom' => 'custom',
            ],
        ]);

        $builder->add('custom_target', TextType::class);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);
        $view->children['type']->vars['attr']['data-field-route-name'] = $view->children['route_name']->vars['id'];
        $view->children['type']->vars['attr']['data-field-route-params'] = $view->children['route_params']->vars['id'];
        $view->children['type']->vars['attr']['data-field-anchor'] = $view->children['anchor']->vars['id'];
        $view->children['type']->vars['attr']['data-field-url'] = $view->children['url']->vars['id'];

        $view->children['target']->vars['attr']['data-field-custom-target'] = $view->children['custom_target']->vars['id'];
    }
}
