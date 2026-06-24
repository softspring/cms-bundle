<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\DependencyInjection\SfsCmsExtension;
use Softspring\CmsBundle\Entity\Block;
use Softspring\CmsBundle\Entity\Content;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Menu;
use Softspring\CmsBundle\Entity\MenuItem;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SfsCmsExtensionTest extends TestCase
{
    public function testPrependAddsDoctrineTwigMigrationsAndAssetMapperConfiguration(): void
    {
        $container = new ContainerBuilder();
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'App\Migrations' => '%kernel.project_dir%/migrations',
            ],
        ]);

        (new SfsCmsExtension())->prepend($container);

        $doctrineConfig = $container->getExtensionConfig('doctrine')[0];
        self::assertSame('App\Entity\Block', $doctrineConfig['orm']['resolve_target_entities'][BlockInterface::class]);
        self::assertSame('App\Entity\Content', $doctrineConfig['orm']['resolve_target_entities'][ContentInterface::class]);
        self::assertSame([
            'is_bundle' => true,
            'mapping' => true,
        ], $doctrineConfig['orm']['mappings']['SfsCmsBundle']);

        $twigConfig = $container->getExtensionConfig('twig')[0];
        self::assertArrayHasKey('version', $twigConfig['globals']['sfs_cms_bundle']);
        self::assertArrayHasKey('version_branch', $twigConfig['globals']['sfs_cms_bundle']);
        self::assertSame('SfsPolymorphicFormType', $twigConfig['paths']['%kernel.project_dir%/vendor/softspring/polymorphic-form-type/templates']);

        $migrationsConfig = $container->getExtensionConfig('doctrine_migrations')[0];
        self::assertSame([
            'App\Migrations' => '%kernel.project_dir%/migrations',
            'Softspring\CmsBundle\Migrations' => '@SfsCmsBundle/src/Migrations',
        ], $migrationsConfig['migrations_paths']);

        if (interface_exists(AssetMapperInterface::class)) {
            $frameworkConfig = $container->getExtensionConfig('framework')[0];
            self::assertArrayHasKey('asset_mapper', $frameworkConfig);
            self::assertArrayHasKey('@softspring/cms-bundle', array_flip($frameworkConfig['asset_mapper']['paths']));
        } else {
            self::assertSame([], $container->getExtensionConfig('framework'));
        }
    }

    public function testProcessDataClassesKeepsDefaultClassesOutOfSuperclassConversionList(): void
    {
        $container = $this->createContainerWithDataClassParameters([
            'sfs_cms.site.class' => Site::class,
            'sfs_cms.route.class' => Route::class,
            'sfs_cms.route.path_class' => RoutePath::class,
            'sfs_cms.content.content_class' => Content::class,
            'sfs_cms.content.content_version_class' => ContentVersion::class,
            'sfs_cms.content.page_class' => Page::class,
            'sfs_cms.menu.class' => Menu::class,
            'sfs_cms.menu.item_class' => MenuItem::class,
            'sfs_cms.block.class' => Block::class,
            'sfs_cms.compiled.class' => 'Softspring\CmsBundle\Entity\CompiledData',
        ]);

        $extension = new TestableSfsCmsExtension();
        $extension->processDataClassesForTest($container);

        self::assertSame([], $container->getParameter('sfs_cms.convert_superclass_list'));
    }

    public function testProcessDataClassesMarksReplacedDefaultClassesAsMappedSuperclasses(): void
    {
        $container = $this->createContainerWithDataClassParameters([
            'sfs_cms.site.class' => 'App\Entity\Site',
            'sfs_cms.route.class' => Route::class,
            'sfs_cms.route.path_class' => RoutePath::class,
            'sfs_cms.content.content_class' => 'App\Entity\Content',
            'sfs_cms.content.content_version_class' => ContentVersion::class,
            'sfs_cms.content.page_class' => Page::class,
            'sfs_cms.menu.class' => Menu::class,
            'sfs_cms.menu.item_class' => MenuItem::class,
            'sfs_cms.block.class' => Block::class,
            'sfs_cms.compiled.class' => 'Softspring\CmsBundle\Entity\CompiledData',
        ]);

        $extension = new TestableSfsCmsExtension();
        $extension->processDataClassesForTest($container);

        self::assertSame([Site::class, Content::class], $container->getParameter('sfs_cms.convert_superclass_list'));
    }

    private function createContainerWithDataClassParameters(array $parameters): ContainerBuilder
    {
        $container = new ContainerBuilder();

        foreach ($parameters as $name => $value) {
            $container->setParameter($name, $value);
        }

        return $container;
    }
}

class TestableSfsCmsExtension extends SfsCmsExtension
{
    public function processDataClassesForTest(ContainerBuilder $container): void
    {
        $this->processDataClasses($container);
    }
}
