<?php

namespace Softspring\CmsBundle\DependencyInjection;

use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\LayoutInterface;
use Softspring\CmsBundle\Model\PageInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SfsCmsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config/services'));

        // set config parameters
        $container->setParameter('sfs_cms.entity_manager_name', $config['entity_manager']);

        // configure site classes
        $container->setParameter('sfs_cms.site.type', $config['site']['type']);
        $container->setParameter('sfs_cms.site.class', $config['site']['class']);
        $container->setParameter('sfs_cms.site.route_param_name', $config['site']['route_param_name'] ?? null);
        $container->setParameter('sfs_cms.site.find_field_name', $config['site']['find_field_name'] ?? null);

        // configure route classes
        $container->setParameter('sfs_cms.route.class', $config['route']['class']);
        $container->setParameter('sfs_cms.route.path_class', $config['route']['path_class']);
        $container->setParameter('sfs_cms.route.find_field_name', $config['route']['find_field_name'] ?? null);

        // configure page classes
        $container->setParameter('sfs_cms.page.page_class', $config['page']['page_class']);
//        $container->setParameter('sfs_cms.page.page_path_class', $config['page']['page_path_class']);
        $container->setParameter('sfs_cms.page.find_field_name', $config['page']['find_field_name'] ?? null);

        // configure layout classes
        $container->setParameter('sfs_cms.layout.class', $config['layout']['class']);
        $container->setParameter('sfs_cms.layout.find_field_name', $config['layout']['find_field_name'] ?? null);

        // configure block classes
        $container->setParameter('sfs_cms.block.class', $config['block']['class'] ?? null);
        $container->setParameter('sfs_cms.block.find_field_name', $config['block']['find_field_name'] ?? null);
        $container->setParameter('sfs_cms.block.types', $config['block']['types'] ?? []);

        $moduleMappings = [];
        $moduleFormMappings = [];
        foreach ($config['modules']['types'] as $key => list('entity' => $entity, 'form' => $form)) {
            $moduleMappings[$key] = $entity;
            $moduleFormMappings[$key] = $form;
        }
        $container->setParameter('sfs_cms.modules.mappings', $moduleMappings);
        $container->setParameter('sfs_cms.modules.mapping_forms', $moduleFormMappings);

        // load services
        $loader->load('services.yaml');
        $loader->load('controller/admin_sites.yaml');
        $loader->load('doctrine_filter.yaml');

        if ($container->getParameter('sfs_cms.block.class')) {
            $loader->load('controller/admin_blocks.yaml');
        }

        true && $loader->load('controller/admin_layout.yaml');
        true && $loader->load('controller/admin_routes.yaml');
        true && $loader->load('controller/admin_pages.yaml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $doctrineConfig = [];

        // add a default config to force load target_entities, will be overwritten by ResolveDoctrineTargetEntityPass
        $doctrineConfig['orm']['resolve_target_entities'][SiteInterface::class] = 'App\Entity\Site';
        $doctrineConfig['orm']['resolve_target_entities'][BlockInterface::class] = 'App\Entity\Block';
        $doctrineConfig['orm']['resolve_target_entities'][LayoutInterface::class] = 'App\Entity\Layout';
        $doctrineConfig['orm']['resolve_target_entities'][PageInterface::class] = 'App\Entity\Page';

        // disable auto-mapping for this bundle to prevent mapping errors
        $doctrineConfig['orm']['mappings']['SfsCmsBundle'] = [
            'is_bundle' => true,
            'mapping' => true,
        ];

        $container->prependExtensionConfig('doctrine', $doctrineConfig);
    }
}
