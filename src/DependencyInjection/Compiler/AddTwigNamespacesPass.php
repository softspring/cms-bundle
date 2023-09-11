<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddTwigNamespacesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.native_filesystem');

        foreach (array_reverse($container->getParameter('sfs_cms.collections')) as $collectionPath) {
            $collectionPath = $container->getParameter('kernel.project_dir').'/'.trim($collectionPath, '/');

            $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$collectionPath, 'cms']);

            foreach (['module', 'block', 'content', 'layout', 'menu', 'site'] as $item) {
                $path = "$collectionPath/{$item}s";
                if (is_dir($path)) {
                    $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$path, $item]);
                }
            }
        }
    }
}
