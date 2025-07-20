<?php

namespace Softspring\CmsBundle\Form\Resolver;

use Softspring\Component\DynamicFormType\Form\Resolver\DefaultTypeResolver;

class CmsTypeResolver extends DefaultTypeResolver
{
    public function getPossibleFormClasses(string $type): array
    {
        return [
            'App\Form\Type\\'.ucfirst($type).'Type',
            'Softspring\CmsBundle\Form\Type\\'.ucfirst($type).'Type',
        ];
    }
}
