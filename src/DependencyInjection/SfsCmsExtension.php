<?php

namespace Softspring\CmsBundle\DependencyInjection;

use Composer\InstalledVersions;
use Softspring\CmsBundle\Config\ConfigLoader;
use Softspring\CmsBundle\Data\EntityTransformer\EntityTransformerInterface;
use Softspring\CmsBundle\Data\FieldTransformer\FieldTransformerInterface;
use Softspring\CmsBundle\Entity;
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
    public function load(array $configs, ContainerBuilder $container): void
    {
        /* @deprecated will be removed on 6.0 version, when fixtures will be refactored to use serializer */
        $container->registerForAutoconfiguration(EntityTransformerInterface::class)->addTag('sfs_cms.data.entity_transformer');
        $container->registerForAutoconfiguration(FieldTransformerInterface::class)->addTag('sfs_cms.data.field_transformer');

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config/services'));

        // prepend default bundle collection
        array_unshift($config['collections'], 'vendor/softspring/cms-bundle/cms');
        // append (last to override anything) the project collection
        array_push($config['collections'], 'cms');
        $container->setParameter('sfs_cms.collections', $config['collections']);

        if ($container->hasParameter('sfs_cms.config_extensions')) {
            $configExtensions = $container->getParameter('sfs_cms.config_extensions');
        }
        $configLoader = new ConfigLoader($container, $config['collections'], $configExtensions ?? []);
        $container->setParameter('sfs_cms.modules', $configLoader->getModules($container));
        $container->setParameter('sfs_cms.layouts', $configLoader->getLayouts($container));
        $container->setParameter('sfs_cms.contents', $configLoader->getContents($container));
        $container->setParameter('sfs_cms.menus', $configLoader->getMenus($container));
        $container->setParameter('sfs_cms.blocks', $configLoader->getBlocks($container));
        $container->setParameter('sfs_cms.sites', $configLoader->getSites($container));
        $container->setParameter('sfs_cms.site_config', $config['site']);
        $container->setParameter('sfs_cms.site.class', $config['site']['class'] ?? null);

        // enable/disable admin load (useful for separated admin/front projects or installations)
        $adminEnabled = $config['admin'];
        $container->setParameter('sfs_cms.admin', $adminEnabled);
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
        $container->setParameter('sfs_cms.content.save_compiled', $config['content']['save_compiled'] ?? null);
        $container->setParameter('sfs_cms.content.prefix_compiled', $config['content']['prefix_compiled'] ?? null);
        $container->setParameter('sfs_cms.content.page_class', $config['content']['page_class'] ?? null);

        // configure menu classes
        $container->setParameter('sfs_cms.menu.class', $config['menu']['class']);
        $container->setParameter('sfs_cms.menu.item_class', $config['menu']['item_class']);
        $container->setParameter('sfs_cms.menu.find_field_name', $config['menu']['find_field_name'] ?? null);

        // configure block classes
        $container->setParameter('sfs_cms.block.class', $config['block']['class'] ?? null);
        $container->setParameter('sfs_cms.block.find_field_name', $config['block']['find_field_name'] ?? null);
        //        $container->setParameter('sfs_cms.block.types', $config['block']['types'] ?? []);

        $this->processDataClasses($container);

        // load services
        $loader->load('services.yaml');
        $adminEnabled && $loader->load('admin_services.yaml');
        $loader->load('entity_transformer.yaml');
        $loader->load('dynamic_form_type.yaml');
        $adminEnabled && $loader->load('controller/admin_blocks.yaml');
        $adminEnabled && $loader->load('controller/admin_content.yaml');
        $adminEnabled && $loader->load('controller/admin_content_version.yaml');
        $adminEnabled && $loader->load('controller/admin_menus.yaml');
        $adminEnabled && $loader->load('controller/admin_routes.yaml');
        $adminEnabled && $loader->load('controller/admin_sites.yaml');

        if (class_exists('Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle')) {
            $loader->load('deprecated_param_converters.yaml');
        }

        $loader->load('data_collector.yaml');

        $version = InstalledVersions::getVersion('symfony/twig-bridge');
        if (version_compare($version, '6.0.0') < 0) {
            $loader->load('data_collector_twig.yaml');
        }

        if (class_exists(MakerBundle::class)) {
            $loader->load('makers.yaml');
        }
    }

    protected function processDataClasses(ContainerBuilder $container): void
    {
        $superClassList = [];

        $defaultClasses = [
            'sfs_cms.site.class' => Entity\Site::class,
            'sfs_cms.route.class' => Entity\Route::class,
            'sfs_cms.route.path_class' => Entity\RoutePath::class,
            'sfs_cms.content.content_class' => Entity\Content::class,
            'sfs_cms.content.content_version_class' => Entity\ContentVersion::class,
            'sfs_cms.content.page_class' => Entity\Page::class,
            'sfs_cms.menu.class' => Entity\Menu::class,
            'sfs_cms.menu.item_class' => Entity\MenuItem::class,
            'sfs_cms.block.class' => Entity\Block::class,
        ];

        foreach ($defaultClasses as $parameter => $defaultEntityClass) {
            if ($container->getParameter($parameter) !== $defaultEntityClass) {
                $superClassList[] = $defaultEntityClass;
            }
        }

        $container->setParameter('sfs_cms.convert_superclass_list', $superClassList);
    }

    public function prepend(ContainerBuilder $container): void
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

        $version = InstalledVersions::getVersion('softspring/cms-bundle');
        if (str_ends_with($version, '-dev')) {
            $version = InstalledVersions::getPrettyVersion('softspring/cms-bundle');
        }
        $container->prependExtensionConfig('twig', [
            'globals' => [
                'sfs_cms_bundle' => [
                    'version' => $version,
                    'version_branch' => str_ends_with($version, '-dev') ? str_replace('.x-dev', '', $version) : false,
                ],
            ],
            'paths' => [
                '%kernel.project_dir%/vendor/softspring/polymorphic-form-type/templates' => 'SfsPolymorphicFormType',
            ],
        ]);

        $doctrineConfig = $container->getExtensionConfig('doctrine_migrations');
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => array_merge(array_pop($doctrineConfig)['migrations_paths'] ?? [], [
                'Softspring\CmsBundle\Migrations' => '@SfsCmsBundle/src/Migrations',
            ]),
        ]);
    }
}
