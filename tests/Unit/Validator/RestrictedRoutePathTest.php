<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Validator;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Validator\RestrictedRoutePath;
use Symfony\Component\Validator\Constraint;

class RestrictedRoutePathTest extends TestCase
{
    public function testConstraintTargetsClasses(): void
    {
        $this->assertSame(Constraint::CLASS_CONSTRAINT, (new RestrictedRoutePath())->getTargets());
    }
}
