<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Softspring\CmsBundle\Render\PageRender;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ModuleRenderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has(PageRender::class)) {
            return;
        }

        $definition = $container->findDefinition(PageRender::class);

        $taggedServices = $container->findTaggedServiceIds('sfs_cms.module_render');

        $moduleRenders = [];
        foreach ($taggedServices as $id => $tags) {
            $moduleRenders[] = new Reference($id);
        }

        $definition->setArgument('$moduleRenders', $moduleRenders);
    }
}