<?php

namespace Softspring\CmsBundle\Test\Unit\Render;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\Site;
use Softspring\CmsBundle\Render\Module\ModuleRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class ModuleSiteEntityFilterTest extends TestCase
{
    public function testHydratedSiteFiltersMatchByConfiguredSite(): void
    {
        $currentSite = new Site();
        $currentSite->setId('primary');
        $otherSite = new Site();
        $otherSite->setId('secondary');
        $storedSite = new Site();
        $storedSite->setId('primary');
        $config = $this->createMock(CmsConfig::class);
        $config->method('getSite')->willReturnCallback(fn (string $id): Site => 'primary' === $id ? $currentSite : $otherSite);
        $request = new Request();
        $request->attributes->set('_sfs_cms_site', $currentSite);
        $stack = new RequestStack();
        $stack->push($request);
        $renderer = new class($config, $stack, new Environment(new ArrayLoader()), null) extends ModuleRenderer {
            public function skips(array $filter): bool
            {
                return $this->skipModuleRenderBySiteFilter(['site_filter' => $filter]);
            }
        };

        self::assertFalse($renderer->skips([$storedSite]));
        self::assertFalse($renderer->skips([$otherSite, $storedSite]));
        self::assertTrue($renderer->skips([$otherSite]));
        self::assertTrue($renderer->skips([]));
        self::assertFalse($renderer->skips(['primary']));
        self::assertFalse($renderer->skips(['primary' => true]));
        self::assertTrue($renderer->skips(['primary' => false]));
    }
}
