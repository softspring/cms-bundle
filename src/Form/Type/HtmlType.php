<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class HtmlType extends AbstractType
{
    public function getParent()
    {
        return TextType::class;
    }
}