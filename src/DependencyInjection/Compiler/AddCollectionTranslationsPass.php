<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class AddCollectionTranslationsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $translator = $container->findDefinition('translator.default');

        $options = $translator->getArgument(4);

        foreach ($container->getParameter('sfs_cms.collections') as $collectionPath) {
            $translationsPath = $container->getParameter('kernel.project_dir').'/'.trim($collectionPath, '/').'/translations';
            if (is_dir($translationsPath)) {
                $options['scanned_directories'][] = $translationsPath;

                // copied from Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension
                $finder = Finder::create()
                    ->followLinks()
                    ->files()
                    ->filter(function (\SplFileInfo $file) {
                        return 2 <= substr_count($file->getBasename(), '.') && preg_match('/\.\w+$/', $file->getBasename());
                    })
                    ->in($translationsPath)
                    ->sortByName()
                ;

                foreach ($finder as $file) {
                    $fileNameParts = explode('.', basename($file));
                    $locale = $fileNameParts[\count($fileNameParts) - 2];

                    if (!isset($options['resource_files'][$locale])) {
                        $options['resource_files'][$locale] = [];
                    }

                    $options['resource_files'][$locale][] = (string) $file;
                }
            }

            // add item's translations if exists
            foreach (['module', 'block', 'content', 'layout', 'menu', 'site'] as $item) {
                $path = $container->getParameter('kernel.project_dir').'/'.trim($collectionPath, '/')."/{$item}s";
                if (is_dir($path)) {
                    foreach ((new Finder())->directories()->in("$path/*")->name('translations') as $transDirectory) {
                        $options['scanned_directories'][] = $transDirectory->getRealPath();
                        foreach ((new Finder())->in($transDirectory->getRealPath())->files() as $file) {
                            $fileNameParts = explode('.', $file->getBasename());
                            $locale = array_pop($fileNameParts);
                            $options['resource_files'][$locale][] = (string) $file;
                        }
                    }
                }
            }
        }

        $translator->replaceArgument(4, $options);
    }
}
