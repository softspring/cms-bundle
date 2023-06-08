<?php

namespace Softspring\CmsBundle\Form\Resolver;

use Softspring\Component\DynamicFormType\Form\Resolver\TypeResolver as BaseTypeResolver;

class TypeResolver extends BaseTypeResolver
{
    protected function getFormClasses(string $type): array
    {
        return [
            'App\Form\Type\\'.ucfirst($type).'Type',
            'Softspring\CmsBundle\Form\Type\\'.ucfirst($type).'Type',
            'Symfony\Component\Form\Extension\Core\Type\\'.ucfirst($type).'Type',
            'Symfony\Bridge\Doctrine\Form\Type\\'.ucfirst($type).'Type',
        ];
    }
}
