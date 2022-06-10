<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType as SymfonyColorType;

class ColorType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'cms_color';
    }

    public function getParent()
    {
        return SymfonyColorType::class;
    }
}