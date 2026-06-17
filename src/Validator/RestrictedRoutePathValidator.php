<?php

namespace Softspring\CmsBundle\Validator;

use Softspring\CmsBundle\Model\RoutePathInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RestrictedRoutePathValidator extends ConstraintValidator
{
    public function __construct(protected array $routeRestrictedPaths)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof RestrictedRoutePath) {
            throw new UnexpectedTypeException($constraint, RestrictedRoutePath::class);
        }

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
