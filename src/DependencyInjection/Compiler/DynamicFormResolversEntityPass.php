<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Softspring\CmsBundle\Form\Resolver\ConstraintResolver;
use Softspring\CmsBundle\Form\Resolver\TypeResolver;
use Softspring\Component\DynamicFormType\Form\Resolver\ConstraintResolverInterface;
use Softspring\Component\DynamicFormType\Form\Resolver\TypeResolverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DynamicFormResolversEntityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $constraintResolver = $container->getDefinition(ConstraintResolverInterface::class);
        $constraintResolver->setClass(ConstraintResolver::class);

        $typeResolver = $container->getDefinition(TypeResolverInterface::class);
        $typeResolver->setClass(TypeResolver::class);
    }
}
