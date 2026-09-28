<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Config;

use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\DisabledModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidBlockException;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Config\Exception\InvalidLayoutException;
use Softspring\CmsBundle\Config\Exception\InvalidMenuException;
use Softspring\CmsBundle\Config\Exception\InvalidModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidSiteException;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Manager\SiteManagerInterface;

class CmsConfigTest extends TestCase
{
    public function testReturnsConfiguredCollectionsAndPlugins(): void
    {
        $cmsConfig = $this->createCmsConfig();

        self::assertSame($this->layouts(), $cmsConfig->getLayouts());
        self::assertSame($this->layouts()['default'], $cmsConfig->getLayout('default'));
        self::assertSame(['html' => $this->modules()['html']], $cmsConfig->getModules());
        self::assertSame($this->modules(), $cmsConfig->getModules(false));
        self::assertSame($this->modules()['html'], $cmsConfig->getModule('html'));
        self::assertSame($this->contents(), $cmsConfig->getContents());
        self::assertSame($this->contents()['page'], $cmsConfig->getContent('page'));
        self::assertSame($this->menus(), $cmsConfig->getMenus());
        self::assertSame($this->menus()['main'], $cmsConfig->getMenu('main'));
        self::assertSame($this->blocks(), $cmsConfig->getBlocks());
        self::assertSame($this->blocks()['hero'], $cmsConfig->getBlock('hero'));
        self::assertSame($this->registeredPlugins(), $cmsConfig->getRegisteredPlugins());
        self::assertTrue($cmsConfig->hasRegisteredPlugin('CmsBlogPlugin'));
        self::assertTrue($cmsConfig->hasRegisteredPlugin('Softspring\CmsBlogPlugin\CmsBlogPlugin'));
        self::assertFalse($cmsConfig->hasRegisteredPlugin('UnknownPlugin'));
    }

    public function testReturnsNullForOptionalMissingConfiguration(): void
    {
        $cmsConfig = $this->createCmsConfig();

        self::assertNull($cmsConfig->getLayout('missing', false));
        self::assertNull($cmsConfig->getModule('missing', false));
        self::assertNull($cmsConfig->getContent('missing', false));
        self::assertNull($cmsConfig->getMenu('missing', false));
        self::assertNull($cmsConfig->getBlock('missing', false));
        self::assertNull($cmsConfig->getSite('missing', false));
    }

    #[DataProvider('requiredConfigurationProvider')]
    public function testThrowsForMissingRequiredConfiguration(string $method, string $exceptionClass): void
    {
        $cmsConfig = $this->createCmsConfig();

        $this->expectException($exceptionClass);

        $cmsConfig->{$method}('missing');
    }

    public static function requiredConfigurationProvider(): iterable
    {
        yield 'layout' => ['getLayout', InvalidLayoutException::class];
        yield 'module' => ['getModule', InvalidModuleException::class];
        yield 'content' => ['getContent', InvalidContentException::class];
        yield 'menu' => ['getMenu', InvalidMenuException::class];
        yield 'block' => ['getBlock', InvalidBlockException::class];
        yield 'site' => ['getSite', InvalidSiteException::class];
    }

    public function testResolvesContentConfigurationFromContentEntity(): void
    {
        $cmsConfig = $this->createCmsConfig();

        self::assertSame($this->contents()['page'], $cmsConfig->getContent(new Page()));
        self::assertSame(['page' => Page::class], $cmsConfig->getContentMappings());
    }

    public function testDisabledModulesAreFilteredAndGuarded(): void
    {
        $cmsConfig = $this->createCmsConfig();

        $this->expectException(DisabledModuleException::class);

        $cmsConfig->getModule('legacy');
    }

    public function testDisabledModuleCanBeReadWhenEnabledFilterIsDisabled(): void
    {
        $cmsConfig = $this->createCmsConfig();

        self::assertSame($this->modules()['legacy'], $cmsConfig->getModule('legacy', true, false));
    }

    public function testSynchronizesConfiguredSitesWithPersistedEntities(): void
    {
        $defaultSite = new Site();
        $defaultSite->setId('default');
        $defaultSite->setConfig([
            'allowed_content_types' => ['legacy_page'],
            'hosts' => [],
        ]);

        $legacySite = new Site();
        $legacySite->setId('legacy');
        $legacySite->setConfig([
            'allowed_content_types' => ['page'],
            'hosts' => [],
        ]);

        $createdSite = new Site();
        $savedSites = [];

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())
            ->method('findAll')
            ->willReturn([$defaultSite, $legacySite]);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects(self::once())
            ->method('getRepository')
            ->willReturn($repository);
        $siteManager->expects(self::once())
            ->method('deleteEntity')
            ->with($legacySite);
        $siteManager->expects(self::once())
            ->method('createEntity')
            ->willReturn($createdSite);
        $siteManager->expects(self::exactly(2))
            ->method('saveEntity')
            ->willReturnCallback(function (object $site) use (&$savedSites): void {
                $savedSites[] = $site;
            });

        $cmsConfig = $this->createCmsConfig($siteManager);

        $sites = $cmsConfig->getSites();

        self::assertSame(['default', 'blog'], array_keys($sites));
        self::assertSame($this->sites()['default'], $defaultSite->getConfig());
        self::assertSame('blog', $createdSite->getId());
        self::assertSame($this->sites()['blog'], $createdSite->getConfig());
        self::assertSame([$defaultSite, $createdSite], $savedSites);
        self::assertSame($defaultSite, $cmsConfig->getSite('default'));
        self::assertSame(['default' => $defaultSite], $cmsConfig->getSitesForContent('page'));
        self::assertSame(['blog' => $createdSite], $cmsConfig->getSitesForContent('post'));

        $cmsConfig->getSites();
    }

    private function createCmsConfig(?SiteManagerInterface $siteManager = null): CmsConfig
    {
        return new CmsConfig(
            $this->layouts(),
            $this->modules(),
            $this->contents(),
            $this->menus(),
            $this->blocks(),
            $this->sites(),
            $siteManager ?? $this->createEmptySiteManager(),
            $this->registeredPlugins(),
        );
    }

    private function createEmptySiteManager(): SiteManagerInterface
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findAll')->willReturn([]);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->method('getRepository')->willReturn($repository);
        $siteManager->method('createEntity')->willReturnCallback(fn (): Site => new Site());

        return $siteManager;
    }

    private function layouts(): array
    {
        return [
            'default' => [
                '_id' => 'default',
                'templates' => ['page' => '@cms/page.html.twig'],
            ],
        ];
    }

    private function modules(): array
    {
        return [
            'html' => [
                '_id' => 'html',
                'enabled' => true,
            ],
            'legacy' => [
                '_id' => 'legacy',
                'enabled' => false,
            ],
        ];
    }

    private function contents(): array
    {
        return [
            'page' => [
                '_id' => 'page',
                'entity_class' => Page::class,
                'default_layout' => 'default',
                'allowed_layouts' => ['default'],
            ],
        ];
    }

    private function menus(): array
    {
        return [
            'main' => [
                '_id' => 'main',
            ],
        ];
    }

    private function blocks(): array
    {
        return [
            'hero' => [
                '_id' => 'hero',
            ],
        ];
    }

    private function sites(): array
    {
        return [
            'default' => [
                'allowed_content_types' => ['page'],
                'hosts' => [
                    ['domain' => 'example.org'],
                ],
            ],
            'blog' => [
                'allowed_content_types' => ['post'],
                'hosts' => [
                    ['domain' => 'blog.example.org'],
                ],
            ],
        ];
    }

    private function registeredPlugins(): array
    {
        return [
            [
                'name' => 'CmsBlogPlugin',
                'class' => 'Softspring\CmsBlogPlugin\CmsBlogPlugin',
                'alias' => 'sfs_cms_blog',
            ],
        ];
    }
}
