<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class AddTranslationsPathsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $translatorDefinition = $container->getDefinition('translator.default');

        foreach ($container->getParameter('sfs_cms.collections') as $collectionPath) {
            // add item's translations if exists
            foreach (['module', 'block', 'content', 'layout', 'menu', 'site'] as $item) {
                $path = $container->getParameter('kernel.project_dir').'/'.trim($collectionPath, '/')."/{$item}s";
                if (is_dir($path)) {
                    foreach ((new Finder())->directories()->in("$path/*")->name('translations') as $transDirectory) {
                        foreach ((new Finder())->in($transDirectory->getRealPath())->files() as $file) {
                            $fileNameParts = explode('.', $file->getBasename());
                            $format = array_pop($fileNameParts);
                            $locale = array_pop($fileNameParts);
                            $domain = implode('.', $fileNameParts);
                            $translatorDefinition->addMethodCall('addResource', [$format, $file->getRealPath(), $locale, $domain]);
                        }
                    }
                }
            }
        }
    }
}
