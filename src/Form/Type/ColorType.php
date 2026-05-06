<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType as SymfonyColorType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'cms_color';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'show_toggler' => true,
        ]);
        $resolver->setAllowedTypes('show_toggler', 'bool');
    }

    public function getParent(): string
    {
        return SymfonyColorType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['show_toggler'] = $options['show_toggler'];
    }
}
