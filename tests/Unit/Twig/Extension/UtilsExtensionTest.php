<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Twig\Extension;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Twig\Extension\UtilsExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class UtilsExtensionTest extends TestCase
{
    public function testItRegistersFiltersAndFunctions(): void
    {
        $extension = new UtilsExtension($this->createMock(ContentManagerInterface::class));

        self::assertSame(['base64_encode', 'base64_decode', 'sfs_cms_format_cache_ttl'], array_map(static fn (TwigFilter $filter): string => $filter->getName(), $extension->getFilters()));

        self::assertSame([
            'sfs_cms_check_content_locales_and_routes',
            'sfs_cms_validate_module_html',
            'sfs_cms_content_type',
            'sfs_cms_render_ajax',
        ], array_map(static fn (TwigFunction $function): string => $function->getName(), $extension->getFunctions()));
    }

    public function testItRendersAjaxContainer(): void
    {
        $extension = new UtilsExtension($this->createMock(ContentManagerInterface::class));

        $html = $extension->renderAjax('/fragment', ['class' => 'slot', 'data-name' => '"quoted"']);

        self::assertStringContainsString('class="slot"', $html);
        self::assertStringContainsString('data-name="&quot;quoted&quot;"', $html);
        self::assertStringContainsString('data-href="/fragment"', $html);
        self::assertStringContainsString('fetch(ajaxDiv.getAttribute', $html);
    }

    public function testItFormatsCacheTtl(): void
    {
        $extension = new UtilsExtension($this->createMock(ContentManagerInterface::class));

        self::assertNull($extension->formatCacheTtl(null));
        self::assertNull($extension->formatCacheTtl(false));
        self::assertSame('0s', $extension->formatCacheTtl(0));
        self::assertSame('60s', $extension->formatCacheTtl(60));
        self::assertSame('30m', $extension->formatCacheTtl(1800));
        self::assertSame('2h', $extension->formatCacheTtl(7200));
        self::assertSame('4d', $extension->formatCacheTtl(345600));
        self::assertSame('30d', $extension->formatCacheTtl(2592000));
    }

    public function testItFindsMissingRouteLocales(): void
    {
        $pathEs = $this->createPath('es');
        $pathEmpty = $this->createPath(null);

        $route = $this->createMock(RouteInterface::class);
        $route->method('getPaths')->willReturn(new ArrayCollection([$pathEs, $pathEmpty]));

        $content = $this->createMock(ContentInterface::class);
        $content->method('getLocales')->willReturn(['en', 'es']);
        $content->method('getRoutes')->willReturn(new ArrayCollection([$route]));

        $extension = new UtilsExtension($this->createMock(ContentManagerInterface::class));

        self::assertSame([
            'locales' => ['en', 'es'],
            'routes_locales' => ['es'],
            'missing_route_locales' => ['en'],
        ], $extension->checkContentLocalesAndRoutes($content));
    }

    private function createPath(?string $locale): RoutePathInterface
    {
        $path = $this->createMock(RoutePathInterface::class);
        $path->method('getLocale')->willReturn($locale);

        return $path;
    }
}
