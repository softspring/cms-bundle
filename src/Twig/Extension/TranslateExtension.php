<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Router\UrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TranslateExtension extends AbstractExtension
{
    protected RequestStack $requestStack;
    protected array $enabledLocales;
    protected UrlGenerator $urlGenerator;

    public function __construct(RequestStack $requestStack, array $enabledLocales, UrlGenerator $urlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->enabledLocales = $enabledLocales;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('sfs_cms_trans', [$this, 'translate'], ['safe'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_available_locales', [$this, 'getAvailableLocales']),
            new TwigFunction('sfs_cms_alternate_urls', [$this, 'getAlternateUrls']),
            new TwigFunction('sfs_cms_locale_paths', [$this, 'getLocalePaths']),
        ];
    }

    public function translate(array $translatableText): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (isset($translatableText[$request->getLocale()])) {
            return $translatableText[$request->getLocale()];
        }

        if (isset($translatableText[$request->getDefaultLocale()])) {
            return $translatableText[$request->getDefaultLocale()];
        }

        return '';
    }

    public function getAvailableLocales(): array
    {
        return $this->enabledLocales;
    }

    public function getAlternateUrls(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $currentLocale = $request->getLocale();

        /** @var ?RoutePathInterface $routePath */
        $routePath = $request->attributes->get('routePath');

        if (!$routePath) {
            return [];
        }

        $alternate = [];

        foreach ($this->enabledLocales as $locale) {
            if ($locale !== $currentLocale) {
                if (isset($alternate[$routePath->getLocale()])) {
                    continue;
                }

                $hasLocalizedRoutePath = (bool) $routePath->getRoute()->getPaths()->filter(fn (RoutePathInterface $routePath) => $routePath->getLocale() == $locale)->count();

                if (!$hasLocalizedRoutePath) {
                    continue;
                }

                $url = $this->urlGenerator->getUrl($routePath->getRoute(), $locale);

                if ('#' === $url) {
                    continue;
                }

                $alternate[$locale] = $url;
            }
        }

        return $alternate;
    }

    public function getLocalePaths(string $defaultRoute = null): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $currentLocale = $request->getLocale();

        /** @var ?RoutePathInterface $routePath */
        $routePath = $request->attributes->get('routePath');

        if (!$routePath) {
            return [];
        }

        $localePaths = [];

        foreach ($this->enabledLocales as $locale) {
            if (isset($localePaths[$locale])) {
                continue;
            }

            $hasLocalizedRoutePath = (bool) $routePath->getRoute()->getPaths()->filter(fn (RoutePathInterface $routePath) => $routePath->getLocale() == $locale)->count();

            if ($hasLocalizedRoutePath) {
                $localePaths[$locale] = $this->urlGenerator->getPath($routePath->getRoute(), $locale);
            } else {
                $localePaths[$locale] = $defaultRoute ? $this->urlGenerator->getPath($defaultRoute, $locale) : '#';
            }
        }

        return $localePaths;
    }
}
