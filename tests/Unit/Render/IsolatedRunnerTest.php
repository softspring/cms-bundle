<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Render;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Render\Isolated\IsolatedRunner;
use Softspring\CmsBundle\Render\Module\ModuleRenderer;
use Softspring\CmsBundle\Render\Module\ModuleRendererFactory;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

class IsolatedRunnerTest extends TestCase
{
    public function testDoesNotResetEncoreEntrypointsByDefault(): void
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects(self::never())->method('reset');

        $runner = $this->createRunner($entrypointLookup);

        self::assertSame('rendered', $runner->isolateRequestRender(Request::create('/fragment'), fn (): string => 'rendered'));
    }

    public function testCanResetEncoreEntrypointsForFullIsolatedRenders(): void
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects(self::once())->method('reset');

        $runner = $this->createRunner($entrypointLookup);

        self::assertSame('rendered', $runner->isolateRequestRender(Request::create('/page'), fn (): string => 'rendered', true));
    }

    public function testEsiCapableRenderDoesNotResetEncoreEntrypointsByDefault(): void
    {
        $entrypointLookup = $this->createMock(EntrypointLookupInterface::class);
        $entrypointLookup->expects(self::never())->method('reset');

        $runner = $this->createRunner($entrypointLookup, Request::create('/current'));

        self::assertSame('rendered', $runner->isolateEsiCapableRequestRender(fn (): string => 'rendered'));
    }

    private function createRunner(EntrypointLookupInterface $entrypointLookup, ?Request $currentRequest = null): IsolatedRunner
    {
        $requestStack = new RequestStack();
        $requestStack->push($currentRequest ?? Request::create('/current'));

        $app = new AppVariable();
        $app->setRequestStack($requestStack);

        $twig = $this->createMock(Environment::class);
        $twig->method('getGlobals')->willReturn(['app' => $app]);
        $twig->expects(self::atLeastOnce())->method('addGlobal');

        $moduleRendererFactory = $this->createMock(ModuleRendererFactory::class);
        $moduleRendererFactory->method('create')->willReturn($this->createMock(ModuleRenderer::class));

        return new IsolatedRunner(
            $requestStack,
            $this->createMock(RouterInterface::class),
            $twig,
            $moduleRendererFactory,
            $entrypointLookup,
        );
    }
}
