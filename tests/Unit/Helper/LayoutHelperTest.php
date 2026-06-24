<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Helper\LayoutHelper;

class LayoutHelperTest extends TestCase
{
    public function testReturnsAvailableLayoutsFilteredByContentCompatibilityAndEnabledStatus(): void
    {
        $helper = new LayoutHelper($this->createCmsConfig([
            'page_default' => [
                '_id' => 'page_default',
                'enabled' => true,
            ],
            'post_only' => [
                '_id' => 'post_only',
                'enabled' => true,
                'compatible_contents' => ['post'],
            ],
            'legacy' => [
                '_id' => 'legacy',
                'enabled' => false,
            ],
        ], [
            'page' => [
                '_id' => 'page',
                'entity_class' => Page::class,
                'default_layout' => 'page_default',
                'allowed_layouts' => [],
            ],
        ]));

        self::assertSame(['page_default'], $helper->getAvailableLayouts(new Page()));
        self::assertSame(['page_default', 'legacy'], $helper->getAvailableLayouts(new Page(), 'legacy'));
    }

    public function testAllowedLayoutsLimitTheAvailableLayouts(): void
    {
        $helper = new LayoutHelper($this->createCmsConfig([
            'default' => [
                '_id' => 'default',
                'enabled' => true,
            ],
            'landing' => [
                '_id' => 'landing',
                'enabled' => true,
            ],
        ], [
            'page' => [
                '_id' => 'page',
                'entity_class' => Page::class,
                'default_layout' => 'default',
                'allowed_layouts' => ['landing'],
            ],
        ]));

        self::assertSame(['landing'], $helper->getAvailableLayouts(new Page()));
    }

    public function testDefaultLayoutFallsBackToFirstAvailableLayout(): void
    {
        $helper = new LayoutHelper($this->createCmsConfig([
            'landing' => [
                '_id' => 'landing',
                'enabled' => true,
            ],
        ], [
            'page' => [
                '_id' => 'page',
                'entity_class' => Page::class,
                'default_layout' => 'missing',
                'allowed_layouts' => ['landing'],
            ],
        ]));

        self::assertSame('landing', $helper->getDefaultLayout(new Page()));
    }

    public function testDefaultLayoutUsesConfiguredDefaultWhenAvailable(): void
    {
        $helper = new LayoutHelper($this->createCmsConfig([
            'default' => [
                '_id' => 'default',
                'enabled' => true,
            ],
            'landing' => [
                '_id' => 'landing',
                'enabled' => true,
            ],
        ], [
            'page' => [
                '_id' => 'page',
                'entity_class' => Page::class,
                'default_layout' => 'default',
                'allowed_layouts' => [],
            ],
        ]));

        self::assertSame('default', $helper->getDefaultLayout(new Page()));
    }

    private function createCmsConfig(array $layouts, array $contents): CmsConfig
    {
        $cmsConfig = $this->createMock(CmsConfig::class);
        $cmsConfig->method('getContent')->willReturn($contents['page']);
        $cmsConfig->method('getLayouts')->willReturn($layouts);
        $cmsConfig->method('getLayout')->willReturnCallback(fn (string $id): ?array => $layouts[$id] ?? null);

        return $cmsConfig;
    }
}
