<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Softspring\TranslatableBundle\Model\Translation;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TranslateExtension extends AbstractExtension
{
    public function __construct(
        protected RequestStack $requestStack,
        protected array $enabledLocales,
        protected UrlGenerator $cmsUrlGenerator,
        protected UrlGeneratorInterface $symfonyUrlGenerator,
    ) {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('sfs_cms_trans', [$this, 'translate'], ['is_safe' => ['html'], 'deprecated' => true]),
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
            new TwigFunction('sfs_cms_canonical_url', [$this, 'getCanonicalUrl']),
        ];
    }

    /**
     * @deprecated
     */
    public function translate(mixed $translatableText): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($translatableText instanceof Translation) {
            return $translatableText->translate($request->getLocale());
        }

        if (!is_array($translatableText)) {
            return '';
        }

        // if it can be converted to a translation object
        if (isset($translatableText['_default'])) {
            return Translation::createFromArray($translatableText)->translate($request->getLocale());
        }

        if (!empty($translatableText[$request->getLocale()])) {
            return $translatableText[$request->getLocale()];
        }

        if (!empty($translatableText[$request->getDefaultLocale()])) {
            return $translatableText[$request->getDefaultLocale()];
        }

        return '';
    }

    /**
     * @deprecated should not be used, locales depends on site
     */
    public function getAvailableLocales(): array
    {
        return $this->enabledLocales;
    }

    public function getAlternateUrls(): array
    {
        $request = $this->requestStack->getCurrentRequest();

        /** @var ?RoutePathInterface $routePath */
        $routePath = $request->attributes->get('routePath');

        $site = $request->attributes->get('_sfs_cms_site');

        $alternates = [];

        foreach ($this->enabledLocales as $locale) {
            if ($routePath) {
                $hasLocalizedRoutePath = (bool) $routePath->getRoute()->getPaths()->filter(fn (RoutePathInterface $routePath) => $routePath->getLocale() == $locale)->count();

                if (!$hasLocalizedRoutePath) {
                    continue;
                }

                $url = $this->cmsUrlGenerator->getUrl($routePath->getRoute(), $locale);

                if ('#' === $url) {
                    continue;
                }

                $alternates[$locale] = $url;
            } else {
                $routeName = $request->attributes->get('_route');
                $routeParams = $request->attributes->get('_route_params');
                $routeParams['_locale'] = $locale;
                unset($routeParams['_sfs_cms_locale']);
                unset($routeParams['_sfs_cms_locale_path']);

                $alternates[$locale] = $routeName ? $this->symfonyUrlGenerator->generate($routeName, $routeParams, UrlGeneratorInterface::ABSOLUTE_URL) : '#';
            }
        }

        return $alternates;
    }

    public function getLocalePaths(?string $defaultRoute = null): array
    {
        $request = $this->requestStack->getCurrentRequest();

        /** @var ?RoutePathInterface $routePath */
        $routePath = $request->attributes->get('routePath');

        $localePaths = [];

        foreach ($this->enabledLocales as $locale) {
            if ($routePath) {
                if (isset($localePaths[$locale])) {
                    continue;
                }

                $hasLocalizedRoutePath = (bool) $routePath->getRoute()->getPaths()->filter(fn (RoutePathInterface $routePath) => $routePath->getLocale() == $locale)->count();

                if ($hasLocalizedRoutePath) {
                    $localePaths[$locale] = $this->cmsUrlGenerator->getPath($routePath->getRoute(), $locale);
                } else {
                    $localePaths[$locale] = $defaultRoute ? $this->cmsUrlGenerator->getPath($defaultRoute, $locale) : '#';
                }
            } else {
                $routeName = $request->attributes->get('_route');
                $routeParams = $request->attributes->get('_route_params', []);
                $routeParams['_locale'] = $locale;
                unset($routeParams['_sfs_cms_locale']);
                unset($routeParams['_sfs_cms_locale_path']);

                // prevent null route name
                if ($routeName) {
                    $localePaths[$locale] = $this->symfonyUrlGenerator->generate($routeName, $routeParams, UrlGeneratorInterface::ABSOLUTE_PATH);
                }
            }
        }

        return $localePaths;
    }

    public function getCanonicalUrl(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }

        $site = $request->attributes->get('_sfs_cms_site');
        $locale = $request->getLocale();

        /** @var ?RoutePathInterface $routePath */
        $routePath = $request->attributes->get('routePath');
        if (!$routePath) {
            return null;
        }

        $content = $routePath->getRoute()->getContent();
        $canonicalPage = $content?->getCanonicalPage();

        if ($canonicalPage) {
            $canonicalRoutePath = $canonicalPage->getCanonicalRoutePath($locale) ?: $canonicalPage->getCanonicalRoutePath($canonicalPage->getDefaultLocale());

            if ($canonicalRoutePath) {
                return $this->cmsUrlGenerator->getUrlFixed($canonicalRoutePath, $site);
            }
        }

        return $this->cmsUrlGenerator->getUrlFixed($routePath, $site);
    }
}
