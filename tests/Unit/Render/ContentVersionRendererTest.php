<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Render;

use Closure;
use RuntimeException;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Render\ContentVersionRenderer;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Softspring\CmsBundle\Render\Isolated\IsolatedRunner;
use Softspring\CmsBundle\Render\Module\ModuleRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Twig\Environment;

class ContentVersionRendererTest extends TestCase
{
    public function testRendersContentVersionWithPrecompiledContainers(): void
    {
        $request = Request::create('/');
        $requestStack = $this->createRequestStack($request);

        $content = $this->createContent('Home', 'route_home');
        $version = $this->createVersion($content);

        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->with('layout.html.twig', [
                'containers' => ['main' => '<p>Ready</p>'],
                'version' => $version,
                'content' => $content,
            ])
            ->willReturn('<html>Ready</html>');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('debug')
            ->with('Rendering Home content version');

        $renderer = $this->createRenderer(
            $requestStack,
            $this->createCmsConfig(),
            $twig,
            $this->createMock(ModuleRenderer::class),
            $logger,
        );

        self::assertSame('<html>Ready</html>', $renderer->render($version, $request, compiledContainers: ['main' => '<p>Ready</p>']));
        self::assertSame('route_home', $request->attributes->get('_route'));
    }

    public function testRendersContainersAndCollectsProfilerData(): void
    {
        $request = Request::create('/');
        $requestStack = $this->createRequestStack($request);

        $content = $this->createContent('Home', 'route_home');
        $version = $this->createVersion($content, [
            'main' => [
                ['_module' => 'headline'],
                ['_module' => 'body'],
            ],
            'aside' => [
                ['_module' => 'cta'],
            ],
        ]);

        $moduleRenderer = $this->createMock(ModuleRenderer::class);
        $moduleRenderer->expects(self::exactly(3))
            ->method('render')
            ->willReturnCallback(function (array $module, array &$profilerDebugCollectorData, array $twigAdditionalContext): string {
                $profilerDebugCollectorData[] = $module['_module'];

                self::assertArrayHasKey('version', $twigAdditionalContext);
                self::assertArrayHasKey('content', $twigAdditionalContext);

                return sprintf('<section>%s</section>', $module['_module']);
            });

        $renderer = $this->createRenderer(
            $requestStack,
            $this->createCmsConfig(),
            $this->createMock(Environment::class),
            $moduleRenderer,
        );

        self::assertSame([
            'main' => '<section>headline</section><section>body</section>',
            'aside' => '<section>cta</section>',
        ], $renderer->renderContainers($version, $request, new RenderErrorList()));
        self::assertSame([
            'main' => ['body'],
            'aside' => ['cta'],
        ], $renderer->getDebugCollectorData());
    }

    public function testWrapsRenderErrors(): void
    {
        $request = Request::create('/');
        $requestStack = $this->createRequestStack($request);

        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willThrowException(new RuntimeException('Broken renderer'));

        $renderer = $this->createRenderer(
            $requestStack,
            $this->createCmsConfig(),
            $twig,
            $this->createMock(ModuleRenderer::class),
        );

        $this->expectException(RenderException::class);
        $this->expectExceptionMessage('Error rendering content version v7');

        $renderer->render($this->createVersion($this->createContent('Home', 'route_home')), $request);
    }

    private function createRenderer(
        RequestStack $requestStack,
        CmsConfig $cmsConfig,
        Environment $twig,
        ModuleRenderer $moduleRenderer,
        ?LoggerInterface $logger = null,
    ): ContentVersionRenderer {
        $isolatedRunner = $this->createMock(IsolatedRunner::class);
        $isolatedRunner->method('isolateRequestRender')
            ->willReturnCallback(function (Request $request, callable $render) use ($twig, $moduleRenderer): mixed {
                return $render($request, $twig, $moduleRenderer);
            });

        return new ContentVersionRenderer(
            $cmsConfig,
            $requestStack,
            $isolatedRunner,
            $logger,
            $this->createMock(Profiler::class),
        );
    }

    private function createRequestStack(Request $request): RequestStack
    {
        $requestStack = new RequestStack();
        Closure::fromCallable([$requestStack, 'push'])($request);

        return $requestStack;
    }

    private function createCmsConfig(): CmsConfig
    {
        $cmsConfig = $this->createMock(CmsConfig::class);
        $cmsConfig->method('getLayout')->willReturn([
            'render_template' => 'layout.html.twig',
            'containers' => [
                'main' => [],
                'aside' => [],
            ],
        ]);

        return $cmsConfig;
    }

    private function createVersion(ContentInterface $content, ?array $data = null): ContentVersionInterface
    {
        $version = $this->createMock(ContentVersionInterface::class);
        $version->method('getContent')->willReturn($content);
        $version->method('getLayout')->willReturn('default');
        $version->method('getData')->willReturn($data);
        $version->method('getVersionNumber')->willReturn(7);
        $version->method('getMedias')->willReturn(new ArrayCollection());
        $version->method('getRoutes')->willReturn(new ArrayCollection());
        $version->method('getSections')->willReturn(new ArrayCollection());

        return $version;
    }

    private function createContent(string $name, string $routeId): ContentInterface
    {
        $route = $this->createMock(RouteInterface::class);
        $route->method('getId')->willReturn($routeId);

        $content = $this->createMock(ContentInterface::class);
        $content->method('getName')->willReturn($name);
        $content->method('getRoutes')->willReturn(new ArrayCollection([$route]));

        return $content;
    }
}
