<?php

namespace Softspring\CmsBundle\DependencyInjection;

use Softspring\CmsBundle\Model\CmsCustomerInterface;
use Softspring\CmsBundle\Model\OrderInterface;
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
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));

        // set config parameters
        $container->setParameter('sfs_cms.entity_manager_name', $config['entity_manager']);
        // configure model classes
        $container->setParameter('sfs_cms.site.class', $config['site']['class']);
        $container->setParameter('sfs_cms.site.route_param_name', $config['site']['route_param_name'] ?? null);
        $container->setParameter('sfs_cms.site.find_field_name', $config['site']['find_field_name'] ?? null);
        // configure model classes
        $container->setParameter('sfs_cms.block.class', $config['block']['class'] ?? null);
        $container->setParameter('sfs_cms.block.find_field_name', $config['block']['find_field_name'] ?? null);
        $container->setParameter('sfs_cms.block.types', $config['block']['types'] ?? []);

        // load services
        $loader->load('services.yaml');
        $loader->load('controller/admin_sites.yaml');
        $loader->load('doctrine_filter.yaml');

        if ($container->getParameter('sfs_cms.block.class')) {
            $loader->load('controller/admin_blocks.yaml');
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        $doctrineConfig = [];

        // add a default config to force load target_entities, will be overwritten by ResolveDoctrineTargetEntityPass
        $doctrineConfig['orm']['resolve_target_entities'][SiteInterface::class] = 'App\Entity\CmsSite';

        // disable auto-mapping for this bundle to prevent mapping errors
        $doctrineConfig['orm']['mappings']['SfsCmsBundle'] = [
            'is_bundle' => true,
            'mapping' => false,
        ];

        $container->prependExtensionConfig('doctrine', $doctrineConfig);
    }
}