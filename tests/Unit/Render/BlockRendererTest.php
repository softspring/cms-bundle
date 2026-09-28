<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Render;

use Closure;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Block;
use Softspring\CmsBundle\Render\BlockRenderer;
use Softspring\CmsBundle\Render\Isolated\IsolatedRunner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\StringLoaderExtension;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

class BlockRendererTest extends TestCase
{
    public function testRendersBlockByTypeUsingConfiguredRenderUrl(): void
    {
        $cmsConfig = $this->createCmsConfig([
            'hero' => [
                'esi' => false,
                'ajax' => false,
                'isolate_request' => true,
                'render_url' => 'app_block_hero',
            ],
        ]);

        $renderer = $this->createRenderer($cmsConfig);

        self::assertSame('render:url:app_block_hero:{"title":"Welcome","_locale":"es","_site":"default"}', $renderer->renderBlockByType('hero', ['title' => 'Welcome'], 'es', 'default'));
        self::assertSame([
            [
                'type' => 'hero',
                'config' => [
                    'esi' => false,
                    'ajax' => false,
                    'isolate_request' => true,
                    'render_url' => 'app_block_hero',
                ],
            ],
        ], $renderer->getDebugCollectorData());
    }

    public function testRendersAjaxBlockByTypeUsingControllerFragment(): void
    {
        $cmsConfig = $this->createCmsConfig([
            'gallery' => [
                'esi' => false,
                'ajax' => true,
                'isolate_request' => false,
                'render_url' => null,
            ],
        ]);

        $renderer = $this->createRenderer($cmsConfig);

        self::assertSame(
            'ajax:url:sfs_cms_block_render_by_type:{"_locale":"en","_site":"site-1","type":"gallery"}:{"data-sfs-cms-ajax":"block","data-sfs-cms-block-type":"gallery"}',
            $renderer->renderBlockByType('gallery')
        );
    }

    public function testEsiBlockByTypeRequiresEsiSupport(): void
    {
        $renderer = $this->createRenderer($this->createCmsConfig([
            'hero' => [
                'esi' => true,
                'ajax' => false,
                'isolate_request' => true,
                'render_url' => null,
            ],
        ]));

        $this->expectExceptionMessage('You must enable esi with framework.esi configuration to use it in CMS');

        $renderer->renderBlockByType('hero');
    }

    public function testRendersStoredBlockWithAjaxFragment(): void
    {
        $block = new Block();
        $block->setType('hero');

        $cmsConfig = $this->createCmsConfig([
            'hero' => [
                'esi' => false,
                'ajax' => true,
                'isolate_request' => true,
                'render_url' => null,
            ],
        ]);

        $renderer = $this->createRenderer($cmsConfig);

        self::assertSame(
            'ajax:controller:Softspring\CmsBundle\Controller\BlockController::renderById:{"_locale":"fr","isolate_request":true}:{"data-sfs-cms-ajax":"block","data-sfs-cms-block-type":"hero"}',
            $renderer->renderBlock($block, 'fr')
        );
    }

    public function testForceRendersEsiBlockThroughRegularRender(): void
    {
        $block = new Block();
        $block->setType('hero');

        $cmsConfig = $this->createCmsConfig([
            'hero' => [
                'esi' => true,
                'ajax' => false,
                'isolate_request' => false,
                'render_url' => 'app_block_hero',
            ],
        ]);

        $renderer = $this->createRenderer($cmsConfig, new Esi());

        self::assertSame(
            'render:url:app_block_hero:{"_locale":"es","isolate_request":false}',
            $renderer->renderBlock($block, 'es', true)
        );
    }

    private function createRenderer(CmsConfig $cmsConfig, ?Esi $esi = null): BlockRenderer
    {
        $request = Request::create('/current?utm=campaign');
        $request->setLocale('en');
        $request->attributes->set('_site', 'site-1');

        $requestStack = new RequestStack();
        Closure::fromCallable([$requestStack, 'push'])($request);

        $isolatedRunner = $this->createMock(IsolatedRunner::class);
        $isolatedRunner->method('isolateEsiCapableRequestRender')
            ->willReturnCallback(fn (callable $render): string => $render(Request::create('/isolated')));

        return new BlockRenderer(
            $requestStack,
            $cmsConfig,
            $this->createTwig(),
            $this->createMock(RouterInterface::class),
            $isolatedRunner,
            null,
            $this->createMock(Profiler::class),
            $esi,
        );
    }

    private function createCmsConfig(array $blocks): CmsConfig
    {
        $cmsConfig = $this->createMock(CmsConfig::class);
        $cmsConfig->method('getBlock')->willReturnCallback(fn (string $type): array => $blocks[$type]);

        return $cmsConfig;
    }

    private function createTwig(): Environment
    {
        $twig = new Environment(new ArrayLoader([]), ['autoescape' => false]);
        $twig->addExtension(new StringLoaderExtension());
        $twig->addFunction(new TwigFunction('render', fn (string $value, ?array $attributes = null): string => $this->formatRender('render', $value, $attributes)));
        $twig->addFunction(new TwigFunction('render_esi', fn (string $value, ?array $attributes = null): string => $this->formatRender('render_esi', $value, $attributes)));
        $twig->addFunction(new TwigFunction('sfs_cms_render_ajax', fn (string $value, ?array $attributes = null): string => $this->formatRender('ajax', $value, $attributes)));
        $twig->addFunction(new TwigFunction('url', fn (string $route, array $params = []): string => 'url:'.$route.':'.json_encode($params, JSON_THROW_ON_ERROR)));
        $twig->addFunction(new TwigFunction('path', fn (string $route, array $params = []): string => 'path:'.$route.':'.json_encode($params, JSON_THROW_ON_ERROR)));
        $twig->addFunction(new TwigFunction('controller', fn (string $controller, array $params = []): string => 'controller:'.$controller.':'.json_encode($params, JSON_THROW_ON_ERROR)));
        $twig->addFunction(new TwigFunction('fragment_uri', fn (string $controller): string => 'fragment:'.$controller));

        return $twig;
    }

    private function formatRender(string $function, string $value, ?array $attributes): string
    {
        return $function.':'.$value.(null !== $attributes ? ':'.json_encode($attributes, JSON_THROW_ON_ERROR) : '');
    }
}
