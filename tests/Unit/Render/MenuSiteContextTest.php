<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Render;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Render\Isolated\IsolatedRunner;
use Softspring\CmsBundle\Render\MenuRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

final class MenuSiteContextTest extends TestCase
{
    public function testExplicitSiteOverridesTheCurrentRequestSite(): void
    {
        self::assertSame('secondary', $this->render('secondary'));
    }

    public function testSiteDefaultsToTheResolvedCurrentSite(): void
    {
        self::assertSame('default', $this->render(null));
    }

    private function render(?string $explicit): string
    {
        $site = new Site();
        $site->setId('default');
        $request = Request::create('https://example.test/en/');
        $request->attributes->set('_sfs_cms_site', $site);
        $stack = new RequestStack();
        $stack->push($request);
        $config = $this->createMock(CmsConfig::class);
        $config->method('getMenu')->willReturn(['esi' => false, 'isolate_request' => true]);
        $twig = new Environment(new ArrayLoader());
        $twig->addFunction(new TwigFunction('controller', static fn ($controller, array $attributes): string => $attributes['_site']));
        $twig->addFunction(new TwigFunction('render', static fn ($value): string => $value));
        $runner = $this->createMock(IsolatedRunner::class);
        $runner->method('isolateEsiCapableRequestRender')->willReturnCallback(static fn (callable $render) => $render());

        return (new MenuRenderer($stack, $config, $twig, $runner, null, null))->renderMenuByType('main', 'en', $explicit);
    }
}
