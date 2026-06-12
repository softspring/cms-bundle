<?php

namespace Softspring\CmsBundle\Validator;

use Softspring\CmsBundle\Model\RoutePathInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RestrictedRoutePathValidator extends ConstraintValidator
{
    public function __construct(protected array $routeRestrictedPaths)
    {
    }

    /**
     * @param RoutePathInterface  $value
     * @param RestrictedRoutePath $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof RoutePathInterface) {
            return;
        }

        $path = $value->getPath();

        foreach ($this->routeRestrictedPaths as $routeRestrictedPath) {
            $regex = '/'.str_replace('/', '\/', trim($routeRestrictedPath, '/')).'/';

            if (preg_match($regex, $path)) {
                $this->context->buildViolation($constraint->restrictedPathMessage)
                    ->atPath('path')
                    ->setParameter('{{ path }}', $value->getPath())
                    ->addViolation();
            }
        }
    }
}
