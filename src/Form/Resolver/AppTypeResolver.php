<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Form\Resolver;

use Softspring\Component\DynamicFormType\Form\Resolver\DefaultTypeResolver;

class AppTypeResolver extends DefaultTypeResolver
{
    public function getPossibleFormClasses(string $type): array
    {
        return [
            'App\Form\Type\\'.ucfirst($type).'Type',
        ];
    }
}
