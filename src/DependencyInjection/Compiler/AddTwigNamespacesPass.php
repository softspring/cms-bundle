<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddTwigNamespacesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.native_filesystem');

        // register project namespaces before collections to allow overriding
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', ['%kernel.project_dir%/cms', 'cms']);
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', ['%kernel.project_dir%/cms/modules', 'module']); // use @module/html/render.html.twig
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', ['%kernel.project_dir%/cms/contents', 'content']); // use @content/article/render.html.twig
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', ['%kernel.project_dir%/cms/blocks', 'block']); // use @block/header/render.html.twig
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', ['%kernel.project_dir%/cms/layouts', 'layout']); // use @layout/default/render.html.twig
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', ['%kernel.project_dir%/cms/menus', 'menu']); // use @menu/main/render.html.twig

        foreach ($container->getParameter('sfs_cms.collections') as $collectionPath) {
            // add modules path if exists
            $modulesPath = $container->getParameter('kernel.project_dir').'/'.trim($collectionPath, '/').'/modules';
            if (is_dir($modulesPath)) {
                $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$modulesPath, 'module']);
            }
        }
    }
}