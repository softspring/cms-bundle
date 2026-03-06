<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Sorts twig namespaces to manage priority:
 * - higher priority for project templates
 * - then bundles&plugins overrides
 * - less priority main bundles templates
 */
class SortTwigNamespaceTemplatesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $this->processNamespace($container, 'SfsCms', 'cms-bundle');
        $this->processNamespace($container, 'SfsMedia', 'media-bundle');
    }

    private function processNamespace(ContainerBuilder $container, string $namespace, string $bundleDir): void
    {
        $projectDir = $container->getParameter('kernel.project_dir');

        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.native_filesystem');

        // get namespace paths and remove addPath
        $methodCalls = $twigFilesystemLoaderDefinition->getMethodCalls();
        $sfsNamespaceCalls = $this->getNamespacePaths($namespace, $methodCalls);
        $twigFilesystemLoaderDefinition->setMethodCalls(array_values($methodCalls));

        // sort paths
        uasort($sfsNamespaceCalls, function (string $a, string $b) use ($namespace, $projectDir, $bundleDir): int {
            return $this->pathPriority($a, $namespace, $projectDir, $bundleDir) <=> $this->pathPriority($b, $namespace, $projectDir, $bundleDir);
        });

        // add paths
        foreach ($sfsNamespaceCalls as $path) {
            $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$path, $namespace]);
        }
    }

    private function pathPriority(string $path, string $namespace, string $projectDir, string $bundleDir): int
    {
        if ($path === "$projectDir/templates/bundles/{$namespace}Bundle") {
            return 0;
        }

        $bundlePath = realpath("$projectDir/vendor/softspring/$bundleDir");
        if ($bundlePath && str_starts_with($path, $bundlePath)) {
            return 2;
        }

        return 1; // resto de bundles
    }

    private function getNamespacePaths(string $namespace, array &$methodCalls): array
    {
        $paths = [];

        foreach ($methodCalls as $i => $call) {
            if ('addPath' === $call[0] && (isset($call[1][1]) && $call[1][1] === $namespace)) {
                unset($methodCalls[$i]);
                $paths[] = $call[1][0];
            }
        }

        return $paths;
    }
}
