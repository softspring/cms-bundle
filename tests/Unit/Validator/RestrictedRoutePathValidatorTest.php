<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Validator;

use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Validator\RestrictedRoutePath;
use Softspring\CmsBundle\Validator\RestrictedRoutePathValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class RestrictedRoutePathValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): RestrictedRoutePathValidator
    {
        return new RestrictedRoutePathValidator(['^/?blog(?:/.*)?$']);
    }

    public function testEscapesSlashesFromConfiguredRegex(): void
    {
        $routePath = new RoutePath();
        $routePath->setPath('blog/test');

        $this->validator->validate($routePath, new RestrictedRoutePath());

        $this->buildViolation('The path "{{ path }}" is restricted.')
            ->atPath('property.path.path')
            ->setParameter('{{ path }}', 'blog/test')
            ->assertRaised();
    }

    public function testDoesNotMatchSimilarPathPrefix(): void
    {
        $routePath = new RoutePath();
        $routePath->setPath('blog-test');

        $this->validator->validate($routePath, new RestrictedRoutePath());

        $this->assertNoViolation();
    }
}
