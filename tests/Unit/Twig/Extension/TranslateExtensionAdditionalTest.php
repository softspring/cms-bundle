<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Twig\Extension;

use Closure;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Softspring\CmsBundle\Twig\Extension\TranslateExtension;
use Softspring\TranslatableBundle\Model\Translation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TranslateExtensionAdditionalTest extends TestCase
{
    public function testRegistersFiltersAndFunctions(): void
    {
        $extension = $this->createExtension();

        self::assertSame(['sfs_cms_trans'], array_map(fn (TwigFilter $filter): string => $filter->getName(), $extension->getFilters()));
        self::assertSame(['sfs_cms_alternate_urls', 'sfs_cms_locale_paths'], array_map(fn (TwigFunction $function): string => $function->getName(), $extension->getFunctions()));
        self::assertSame(['es', 'en'], $extension->getAvailableLocales());
    }

    public function testTranslatesArraysAndTranslationObjects(): void
    {
        $extension = $this->createExtension($this->createRequestStack());

        self::assertSame('Hola', $extension->translate(['es' => 'Hola', 'en' => 'Hello']));
        self::assertSame('Hello', $extension->translate(['en' => 'Hello']));
        self::assertSame('', $extension->translate('plain text'));
        self::assertSame('Hola default', $extension->translate(['_default' => 'es', 'es' => 'Hola default', 'en' => 'Hello default']));
        self::assertSame('Hola object', $extension->translate(Translation::createFromArray(['_default' => 'en', 'es' => 'Hola object', 'en' => 'Hello object'])));
    }

    public function testGetsAlternateUrlsFromContentRoutes(): void
    {
        $spanishRoute = $this->createRoute('spanish_route', ['es']);
        $englishRoute = $this->createRoute('english_route', ['en']);

        $content = new Page();
        $content->addRoute($spanishRoute);
        $content->addRoute($englishRoute);

        $request = Request::create('/');
        $request->attributes->set('_content', $content);

        $cmsUrlGenerator = $this->createMock(UrlGenerator::class);
        $cmsUrlGenerator->expects(self::exactly(2))
            ->method('getUrl')
            ->willReturnCallback(fn (RouteInterface $route, string $locale): string => sprintf('https://example.org/%s/%s', $locale, $route->getId()));

        self::assertSame([
            'es' => 'https://example.org/es/spanish_route',
            'en' => 'https://example.org/en/english_route',
        ], $this->createExtension($this->createRequestStack($request), cmsUrlGenerator: $cmsUrlGenerator)->getAlternateUrls());
    }

    public function testGetsAlternateUrlsFromCurrentSymfonyRoute(): void
    {
        $request = Request::create('/');
        $request->attributes->set('_route', 'app_home');
        $request->attributes->set('_route_params', [
            'slug' => 'home',
            '_sfs_cms_locale' => 'es',
            '_sfs_cms_locale_path' => '/es',
        ]);

        $symfonyUrlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $symfonyUrlGenerator->expects(self::exactly(2))
            ->method('generate')
            ->willReturnCallback(fn (string $route, array $params, int $referenceType): string => json_encode([$route, $params, $referenceType], JSON_THROW_ON_ERROR));

        self::assertSame([
            'es' => '["app_home",{"slug":"home","_locale":"es"},0]',
            'en' => '["app_home",{"slug":"home","_locale":"en"},0]',
        ], $this->createExtension($this->createRequestStack($request), symfonyUrlGenerator: $symfonyUrlGenerator)->getAlternateUrls());
    }

    public function testGetsLocalePathsFromRoutePath(): void
    {
        $route = $this->createRoute('cms_page', ['es']);
        $routePath = $this->createRoutePath('es');
        $routePath->method('getRoute')->willReturn($route);

        $request = Request::create('/');
        $request->attributes->set('routePath', $routePath);

        $cmsUrlGenerator = $this->createMock(UrlGenerator::class);
        $cmsUrlGenerator->expects(self::exactly(2))
            ->method('getPath')
            ->willReturnCallback(fn ($routeOrName, string $locale): string => sprintf('/%s/%s', $locale, $routeOrName instanceof RouteInterface ? $routeOrName->getId() : $routeOrName));

        self::assertSame([
            'es' => '/es/cms_page',
            'en' => '/en/cms_home',
        ], $this->createExtension($this->createRequestStack($request), cmsUrlGenerator: $cmsUrlGenerator)->getLocalePaths('cms_home'));
    }

    public function testGetsLocalePathsFromCurrentSymfonyRoute(): void
    {
        $request = Request::create('/');
        $request->attributes->set('_route', 'app_home');
        $request->attributes->set('_route_params', ['slug' => 'home', '_sfs_cms_locale' => 'es']);

        $symfonyUrlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $symfonyUrlGenerator->expects(self::exactly(2))
            ->method('generate')
            ->willReturnCallback(fn (string $route, array $params, int $referenceType): string => json_encode([$route, $params, $referenceType], JSON_THROW_ON_ERROR));

        self::assertSame([
            'es' => '["app_home",{"slug":"home","_locale":"es"},1]',
            'en' => '["app_home",{"slug":"home","_locale":"en"},1]',
        ], $this->createExtension($this->createRequestStack($request), symfonyUrlGenerator: $symfonyUrlGenerator)->getLocalePaths());
    }

    private function createExtension(
        ?RequestStack $requestStack = null,
        ?UrlGenerator $cmsUrlGenerator = null,
        ?UrlGeneratorInterface $symfonyUrlGenerator = null,
    ): TranslateExtension {
        return new TranslateExtension(
            $requestStack ?? $this->createRequestStack(),
            ['es', 'en'],
            $cmsUrlGenerator ?? $this->createMock(UrlGenerator::class),
            $symfonyUrlGenerator ?? $this->createMock(UrlGeneratorInterface::class),
        );
    }

    private function createRequestStack(?Request $request = null): RequestStack
    {
        $request ??= Request::create('/');
        $request->setLocale('es');
        $request->setDefaultLocale('en');

        $requestStack = new RequestStack();
        Closure::fromCallable([$requestStack, 'push'])($request);

        return $requestStack;
    }

    private function createRoute(string $id, array $locales): RouteInterface
    {
        $route = $this->createMock(RouteInterface::class);
        $route->method('getId')->willReturn($id);
        $route->method('getPathForLocale')->willReturnCallback(fn (string $locale): ?RoutePathInterface => in_array($locale, $locales, true) ? $this->createRoutePath($locale) : null);
        $route->method('getPaths')->willReturnCallback(fn (): ArrayCollection => new ArrayCollection(array_map(fn (string $locale): RoutePathInterface => $this->createRoutePath($locale), $locales)));

        return $route;
    }

    /**
     * @return RoutePathInterface&MockObject
     */
    private function createRoutePath(string $locale): RoutePathInterface
    {
        $path = $this->createMock(RoutePathInterface::class);
        $path->method('getLocale')->willReturn($locale);

        return $path;
    }
}
