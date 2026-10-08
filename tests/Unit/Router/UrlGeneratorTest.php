<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Router;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\Site;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UrlGeneratorTest extends TestCase
{
    public function testDisabledOnlyRouteDoesNotGeneratePublicUrl(): void
    {
        $site = new Site();
        $site->setId('disabled');
        $site->setConfig(['enabled' => false, 'hosts' => []]);

        $route = new Route();
        $route->setId('disabled_route');
        $route->addSite($site);

        $path = new RoutePath();
        $path->setLocale('en');
        $path->setCompiledPath('disabled-path');
        $route->addPath($path);

        $requestStack = new RequestStack();
        $requestStack->push(Request::create('https://example.org/en/'));

        $urlGenerator = new UrlGenerator(
            $requestStack,
            $this->createMock(RouteManagerInterface::class),
            $this->createMock(CmsConfig::class),
            ['identification' => 'domain'],
            null,
        );

        $this->assertSame('#', $urlGenerator->getUrl($route, 'en', $site));
        $this->assertSame('#', $urlGenerator->getUrlFixed($path, $site));
    }
}
