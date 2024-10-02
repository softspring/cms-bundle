<?php

namespace Softspring\CmsBundle\Form\Resolver;

use Softspring\Component\DynamicFormType\Form\Resolver\ConstraintResolver as BaseConstraintResolver;
use Softspring\Component\DynamicFormType\Form\Resolver\ConstraintResolverInterface;

class ConstraintResolver extends BaseConstraintResolver implements ConstraintResolverInterface
{
    protected function getConstraintClasses(string $type): array
    {
        return [
            'App\Validator\Constraints\\'.ucfirst($type),
            'Softspring\CmsBundle\Validator\Constraints\\'.ucfirst($type),
            'Softspring\CmsBundle\Validator\\'.ucfirst($type),
            'Symfony\Component\Validator\Constraints\\'.ucfirst($type),
        ];
    }
}
