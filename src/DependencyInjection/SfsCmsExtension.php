<?php

namespace Softspring\CmsBundle\DependencyInjection;

use Softspring\CmsBundle\Config\ConfigLoader;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SfsCmsExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config/services'));

        $configLoader = new ConfigLoader($container);
        $container->setParameter('sfs_cms.modules', $configLoader->getModules($container));
        $container->setParameter('sfs_cms.layouts', $configLoader->getLayouts($container));
        $container->setParameter('sfs_cms.contents', $configLoader->getContents($container));
        $container->setParameter('sfs_cms.blocks', $configLoader->getBlocks($container));
        $container->setParameter('sfs_cms.menus', $configLoader->getMenus($container));


//        $container->setParameter('sfs_cms.dynamic_modules', $config['dynamic_modules']);

        // set config parameters
        $container->setParameter('sfs_cms.entity_manager_name', $config['entity_manager']);

//        // configure site classes
//        $container->setParameter('sfs_cms.site.type', $config['site']['type']);
//        $container->setParameter('sfs_cms.site.class', $config['site']['class']);
//        $container->setParameter('sfs_cms.site.route_param_name', $config['site']['route_param_name'] ?? null);
//        $container->setParameter('sfs_cms.site.find_field_name', $config['site']['find_field_name'] ?? null);

        // configure route classes
        $container->setParameter('sfs_cms.route.class', $config['route']['class']);
        $container->setParameter('sfs_cms.route.path_class', $config['route']['path_class']);
        $container->setParameter('sfs_cms.route.find_field_name', $config['route']['find_field_name'] ?? null);

        // configure content classes
        $container->setParameter('sfs_cms.content.content_class', $config['content']['content_class']);
        $container->setParameter('sfs_cms.content.content_version_class', $config['content']['content_version_class']);
        $container->setParameter('sfs_cms.content.find_field_name', $config['content']['find_field_name'] ?? null);

        // configure menu classes
        $container->setParameter('sfs_cms.menu.menu_class', $config['menu']['menu_class']);
        $container->setParameter('sfs_cms.menu.menu_item_class', $config['menu']['menu_item_class']);
        $container->setParameter('sfs_cms.menu.find_field_name', $config['menu']['find_field_name'] ?? null);

        // configure block classes
//        $container->setParameter('sfs_cms.block.class', $config['block']['class'] ?? null);
//        $container->setParameter('sfs_cms.block.find_field_name', $config['block']['find_field_name'] ?? null);
//        $container->setParameter('sfs_cms.block.types', $config['block']['types'] ?? []);

        // load services
        $loader->load('services.yaml');
//        $loader->load('controller/admin_sites.yaml');
//        $loader->load('doctrine_filter.yaml');

//        if ($container->getParameter('sfs_cms.block.class')) {
//            $loader->load('controller/admin_blocks.yaml');
//        }

//        true && $loader->load('controller/admin_layout.yaml');
        true && $loader->load('controller/admin_routes.yaml');
        true && $loader->load('controller/admin_content.yaml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $doctrineConfig = [];

        // add a default config to force load target_entities, will be overwritten by ResolveDoctrineTargetEntityPass
//        $doctrineConfig['orm']['resolve_target_entities'][SiteInterface::class] = 'App\Entity\Site';
//        $doctrineConfig['orm']['resolve_target_entities'][BlockInterface::class] = 'App\Entity\Block';
        $doctrineConfig['orm']['resolve_target_entities'][ContentInterface::class] = 'App\Entity\Content';

        // disable auto-mapping for this bundle to prevent mapping errors
        $doctrineConfig['orm']['mappings']['SfsCmsBundle'] = [
            'is_bundle' => true,
            'mapping' => true,
        ];

        $container->prependExtensionConfig('doctrine', $doctrineConfig);

        $container->prependExtensionConfig('twig', [
            'paths' => [
                '%kernel.project_dir%/cms'=> 'cms',
                '%kernel.project_dir%/cms/modules'=> 'module', // use @module/html/render.html.twig
                '%kernel.project_dir%/cms/contents'=> 'content', // use @content/article/render.html.twig
                '%kernel.project_dir%/cms/blocks'=> 'block', // use @block/header/render.html.twig
                '%kernel.project_dir%/cms/layouts'=> 'layout', // use @layout/default/render.html.twig
                '%kernel.project_dir%/cms/menus'=> 'menu', // use @menu/main/render.html.twig
            ],
        ]);
    }
}
