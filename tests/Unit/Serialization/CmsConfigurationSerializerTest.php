<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Serialization;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Serialization\CmsConfigurationSerializer;
use Softspring\CmsBundle\Serialization\SensitiveValueSanitizer;
use Softspring\CmsBundle\Serialization\SiteSerializer;

class CmsConfigurationSerializerTest extends TestCase
{
    public function testItSerializesCounts(): void
    {
        $serializer = $this->serializer($this->cmsConfig());

        self::assertSame([
            'sites' => 1,
            'layouts' => 1,
            'modules' => 2,
            'enabledModules' => 1,
            'contents' => 1,
            'menus' => 1,
            'blocks' => 1,
            'plugins' => 1,
        ], $serializer->counts());
    }

    public function testItSerializesConfigurationSectionsWithSanitizedRawConfig(): void
    {
        $serializer = $this->serializer($this->cmsConfig());

        self::assertSame(['required' => true, 'allowedModules' => ['text']], $serializer->layouts(true)['default']['containers']['main']);
        self::assertSame('[redacted]', $serializer->layouts(true)['default']['config']['api_key']);
        self::assertSame(['title'], $serializer->modules(false, true)['text']['optionKeys']);
        self::assertArrayNotHasKey('disabled', $serializer->modules(false, false));
        self::assertSame(['main'], $serializer->contents(false)['page']['containers']['main']['allowedModules']);
        self::assertSame('menu.html.twig', $serializer->menus(false)['main']['renderTemplate']);
        self::assertSame(['title'], $serializer->blocks(false)['promo']['formFieldKeys']);
        self::assertSame('[redacted]', $serializer->plugins()[0]['secret']);
    }

    public function testItSerializesSitesThroughSiteSerializer(): void
    {
        $serializer = $this->serializer($this->cmsConfig());

        self::assertSame('main.example.test', $serializer->sites(false)['main']['canonical']['host']);
    }

    private function serializer(CmsConfig $cmsConfig): CmsConfigurationSerializer
    {
        $sanitizer = new SensitiveValueSanitizer();

        return new CmsConfigurationSerializer(
            $cmsConfig,
            new SiteSerializer($sanitizer),
            $sanitizer,
        );
    }

    private function cmsConfig(): CmsConfig
    {
        $cmsConfig = $this->createMock(CmsConfig::class);
        $cmsConfig->method('getSites')->willReturn(['main' => $this->site()]);
        $cmsConfig->method('getLayouts')->willReturn([
            'default' => [
                'revision' => 1,
                'enabled' => true,
                'save_compiled' => true,
                'render_template' => 'layout.html.twig',
                'edit_template' => 'layout_edit.html.twig',
                'compatible_contents' => ['page'],
                'containers' => [
                    'main' => [
                        'required' => true,
                        'allowed_modules' => ['text'],
                    ],
                ],
                'api_key' => 'secret',
            ],
        ]);
        $cmsConfig->method('getModules')->willReturnCallback(static fn (bool $onlyEnabled = true): array => array_filter([
            'text' => [
                'revision' => 1,
                'enabled' => true,
                'group' => 'content',
                'module_type' => 'text',
                'module_options' => ['title' => []],
            ],
            'disabled' => [
                'revision' => 1,
                'enabled' => false,
            ],
        ], static fn (array $module): bool => !$onlyEnabled || $module['enabled']));
        $cmsConfig->method('getContents')->willReturn([
            'page' => [
                'revision' => 1,
                'entity_class' => 'Page',
                'default_layout' => 'default',
                'allowed_layouts' => ['default'],
                'containers' => [
                    'main' => [
                        'allowed_modules' => ['main'],
                    ],
                ],
            ],
        ]);
        $cmsConfig->method('getMenus')->willReturn([
            'main' => [
                'revision' => 1,
                'render_template' => 'menu.html.twig',
            ],
        ]);
        $cmsConfig->method('getBlocks')->willReturn([
            'promo' => [
                'revision' => 1,
                'enabled' => true,
                'form_fields' => ['title' => []],
            ],
        ]);
        $cmsConfig->method('getRegisteredPlugins')->willReturn([
            [
                'name' => 'analytics',
                'secret' => 'plugin-secret',
            ],
        ]);

        return $cmsConfig;
    }

    private function site(): SiteInterface
    {
        $site = $this->createMock(SiteInterface::class);
        $site->method('getId')->willReturn('main');
        $site->method('getCanonicalScheme')->willReturn('https');
        $site->method('getCanonicalHost')->willReturn('main.example.test');
        $site->method('getCanonicalPort')->willReturn(null);
        $site->method('getConfig')->willReturn([
            'locales' => ['en'],
            'default_locale' => 'en',
        ]);
        $site->method('getMetadata')->willReturn([]);

        return $site;
    }
}
