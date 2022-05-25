<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InjectWebDebugToolbarListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('web_profiler.debug_toolbar')) {
            return;
        }

        $container->addAliases([
            'Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener' => 'web_profiler.debug_toolbar',
        ]);

        $container->getAlias('Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener')->setPublic(true);
    }
}
