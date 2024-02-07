<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class AddTwigBundlesNamespacesPass implements CompilerPassInterface
{
    public function __construct(protected string $templatesPath)
    {
    }

    public function process(ContainerBuilder $container): void
    {
        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.native_filesystem');

        // register project namespaces before collections to allow overriding
        foreach ((new Finder())->in(trim($this->templatesPath).'/bundles')->depth(0)->directories() as $directory) {
            $twigFilesystemLoaderDefinition->addMethodCall('prependPath', [$directory->getRealPath(), str_replace('Bundle', '', $directory->getBasename())]);
        }
    }
}
