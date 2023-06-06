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
            'choice_translation_domain' => 'sfs_cms_types',
            'choices' => [
                'link_type.type.values.anchor' => 'anchor',
                'link_type.type.values.route' => 'route',
                'link_type.type.values.url' => 'url',
            ],
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
