<?php

namespace Softspring\CmsBundle\DependencyInjection;

use Softspring\CmsBundle\Config\ConfigLoader;
use Softspring\CmsBundle\Data\Transformer\DataTransformerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SfsCmsExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(DataTransformerInterface::class)->addTag('sfs_cms.data_transformer');

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config/services'));

        // prepend default bundle collection
        array_unshift($config['collections'], 'vendor/softspring/cms-bundle/cms');
        // append (last to override anything) the project collection
        array_push($config['collections'], 'cms');
        $container->setParameter('sfs_cms.collections', $config['collections']);

        $configLoader = new ConfigLoader($container, $config['collections']);
        $container->setParameter('sfs_cms.modules', $configLoader->getModules($container));
        $container->setParameter('sfs_cms.layouts', $configLoader->getLayouts($container));
        $container->setParameter('sfs_cms.contents', $configLoader->getContents($container));
        $container->setParameter('sfs_cms.menus', $configLoader->getMenus($container));
        $container->setParameter('sfs_cms.blocks', $configLoader->getBlocks($container));
        $container->setParameter('sfs_cms.sites', $configLoader->getSites($container));
        $container->setParameter('sfs_cms.site_config', $config['site']);

        // set config parameters
        $container->setParameter('sfs_cms.entity_manager_name', $config['entity_manager']);

        // configure route classes
        $container->setParameter('sfs_cms.route.class', $config['route']['class']);
        $container->setParameter('sfs_cms.route.path_class', $config['route']['path_class']);
        $container->setParameter('sfs_cms.route.find_field_name', $config['route']['find_field_name'] ?? null);

        // configure content classes
        $container->setParameter('sfs_cms.content.content_class', $config['content']['content_class']);
        $container->setParameter('sfs_cms.content.content_version_class', $config['content']['content_version_class']);
        $container->setParameter('sfs_cms.content.find_field_name', $config['content']['find_field_name'] ?? null);

        // configure menu classes
        $container->setParameter('sfs_cms.menu.class', $config['menu']['class']);
        $container->setParameter('sfs_cms.menu.item_class', $config['menu']['item_class']);
        $container->setParameter('sfs_cms.menu.find_field_name', $config['menu']['find_field_name'] ?? null);

        // configure block classes
        $container->setParameter('sfs_cms.block.class', $config['block']['class'] ?? null);
        $container->setParameter('sfs_cms.block.find_field_name', $config['block']['find_field_name'] ?? null);
        //        $container->setParameter('sfs_cms.block.types', $config['block']['types'] ?? []);

        // load services
        $loader->load('services.yaml');
        $loader->load('controller/admin_routes.yaml');
        $loader->load('controller/admin_content.yaml');

        $loader->load('data_collector.yaml');

        if (class_exists(MakerBundle::class)) {
            $loader->load('makers.yaml');
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        $doctrineConfig = [];

        // add a default config to force load target_entities, will be overwritten by ResolveDoctrineTargetEntityPass
        $doctrineConfig['orm']['resolve_target_entities'][BlockInterface::class] = 'App\Entity\Block';
        $doctrineConfig['orm']['resolve_target_entities'][ContentInterface::class] = 'App\Entity\Content';

        // disable auto-mapping for this bundle to prevent mapping errors
        $doctrineConfig['orm']['mappings']['SfsCmsBundle'] = [
            'is_bundle' => true,
            'mapping' => true,
        ];

        $container->prependExtensionConfig('doctrine', $doctrineConfig);

        $container->prependExtensionConfig('twig', [
            'paths' => [
                '%kernel.project_dir%/vendor/softspring/polymorphic-form-type/templates' => 'SfsPolymorphicFormType',
            ],
        ]);
    }
}
