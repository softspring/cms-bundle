<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Twig\Extension\Admin;

use Twig\TwigFilter;
use Twig\TwigFunction;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Admin\Menu\MenuManager;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Twig\Extension\Admin\AdminExtension;
use Symfony\Component\Routing\RouterInterface;

class AdminExtensionAdditionalTest extends TestCase
{
    public function testRegistersGlobalsFiltersAndFunctions(): void
    {
        $extension = $this->createExtension(contentRecompileEnabled: true);

        self::assertSame(['sfs_cms_admin_content_recompile_enabled' => true], $extension->getGlobals());
        self::assertSame(['sfs_cms_admin_content_url'], array_map(fn (TwigFilter $filter): string => $filter->getName(), $extension->getFilters()));
        self::assertSame([
            'sfs_cms_admin_content_url',
            'sfs_cms_admin_content_menu',
            'sfs_cms_admin_site_menu',
            'sfs_cms_admin_search_content_esi_calls',
            'sfs_cms_admin_search_content_ajax_calls',
        ], array_map(fn (TwigFunction $function): string => $function->getName(), $extension->getFunctions()));
    }

    public function testGeneratesContentAdminUrl(): void
    {
        $content = new Page();

        $contentManager = $this->createMock(ContentManagerInterface::class);
        $contentManager->expects(self::once())
            ->method('getType')
            ->with($content)
            ->willReturn('page');

        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('sfs_cms_admin_content_page_preview', ['content' => $content])
            ->willReturn('/admin/cms/page/preview');

        self::assertSame('/admin/cms/page/preview', $this->createExtension(router: $router, contentManager: $contentManager)->getContentUrl($content, 'preview'));
    }

    public function testDelegatesContentAndSiteMenusToMenuManager(): void
    {
        $content = new Page();
        $site = new Site();

        $menuManager = $this->createMock(MenuManager::class);
        $menuManager->expects(self::exactly(2))
            ->method('getEntityMenu')
            ->willReturnCallback(function (string $menuId, string $current, object $entity) use ($content, $site): array {
                return [$menuId, $current, $entity === $content || $entity === $site];
            });

        $extension = $this->createExtension(menuManager: $menuManager);

        self::assertSame(['content', 'details', true], $extension->getContentMenu('details', $content));
        self::assertSame(['site', 'settings', true], $extension->getSiteMenu('settings', $site));
    }

    public function testSearchesContentEsiCalls(): void
    {
        $cmsConfig = $this->createMock(CmsConfig::class);
        $cmsConfig->expects(self::once())
            ->method('getBlock')
            ->with('hero', false)
            ->willReturn(['name' => 'Hero']);
        $cmsConfig->expects(self::once())
            ->method('getMenu')
            ->with('main', false)
            ->willReturn(['name' => 'Main menu']);

        $content = sprintf(
            "<esi:include src=\"%s\" />\n<esi:include src=\"%s\" />\n<esi:include src=\"%s\" />",
            $this->fragmentUrl('Softspring\CmsBundle\Controller\BlockController::renderByType', ['type' => 'hero']),
            $this->fragmentUrl('Softspring\CmsBundle\Controller\MenuController::renderByType', ['type' => 'main']),
            $this->fragmentUrl('App\Controller\UnknownController::render', []),
        );

        $calls = $this->createExtension(cmsConfig: $cmsConfig)->searchContentEsiCalls($content);

        self::assertCount(3, $calls);
        self::assertSame('block', $calls[0]['processed']['type']);
        self::assertSame('hero', $calls[0]['processed']['block_type']);
        self::assertSame(['name' => 'Hero'], $calls[0]['processed']['block_config']);
        self::assertSame('menu', $calls[1]['processed']['type']);
        self::assertSame('main', $calls[1]['processed']['menu_type']);
        self::assertSame(['name' => 'Main menu'], $calls[1]['processed']['menu_config']);
        self::assertSame('unknown', $calls[2]['processed']['type']);
    }

    public function testSearchesContentAjaxCalls(): void
    {
        $cmsConfig = $this->createMock(CmsConfig::class);
        $cmsConfig->expects(self::once())
            ->method('getBlock')
            ->with('hero', false)
            ->willReturn(['name' => 'Hero']);
        $cmsConfig->expects(self::once())
            ->method('getMenu')
            ->with('main', false)
            ->willReturn(['name' => 'Main menu']);

        $calls = $this->createExtension(cmsConfig: $cmsConfig)->searchContentAjaxCalls(
            '<div data-sfs-cms-ajax="block" data-sfs-cms-block-id="block-1" data-sfs-cms-block-type="hero"></div>'.
            '<div data-sfs-cms-ajax="menu" data-sfs-cms-menu-type="main"></div>'
        );

        self::assertCount(2, $calls);
        self::assertSame([
            'type' => 'block',
            'block_id' => 'block-1',
            'block_type' => 'hero',
            'url' => null,
            'section_id' => null,
            'block_config' => ['name' => 'Hero'],
            'menu_config' => null,
        ], $calls[0]['processed']);
        self::assertSame('menu', $calls[1]['processed']['type']);
        self::assertSame(['name' => 'Main menu'], $calls[1]['processed']['menu_config']);
    }

    private function createExtension(
        ?CmsConfig $cmsConfig = null,
        ?RouterInterface $router = null,
        ?ContentManagerInterface $contentManager = null,
        ?MenuManager $menuManager = null,
        bool $contentRecompileEnabled = false,
    ): AdminExtension {
        return new AdminExtension(
            $cmsConfig ?? $this->createMock(CmsConfig::class),
            $router ?? $this->createMock(RouterInterface::class),
            $contentManager ?? $this->createMock(ContentManagerInterface::class),
            $menuManager ?? $this->createMock(MenuManager::class),
            $contentRecompileEnabled,
            null,
        );
    }

    private function fragmentUrl(string $controller, array $params): string
    {
        return '/_fragment?'.http_build_query([
            '_path' => http_build_query(['_controller' => $controller] + $params),
        ]);
    }
}
