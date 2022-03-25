<?php

namespace Softspring\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class IdType extends AbstractType
{
    public function getParent(): ?string
    {
        return TextType::class;
    }
}