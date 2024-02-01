<?php

namespace Softspring\CmsBundle\Routing;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CmsRouter implements RouterInterface, RequestMatcherInterface, WarmableInterface, ServiceSubscriberInterface
{
    protected Router $staticRouter;
    protected UrlMatcher $urlMatcher;
    protected UrlGenerator $urlGenerator;
    protected ?LoggerInterface $logger;

    public function __construct(Router $staticRouter, UrlMatcher $urlMatcher, UrlGenerator $urlGenerator, ?LoggerInterface $cmsLogger)
    {
        $this->staticRouter = $staticRouter;
        $this->urlMatcher = $urlMatcher;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $cmsLogger;
    }

    public function setContext(RequestContext $context): void
    {
        $this->staticRouter->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->staticRouter->getContext();
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->staticRouter->getRouteCollection();
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        try {
            $cleanParams = array_filter($parameters, fn ($key) => !in_array($key, ['_sfs_cms_locale', '_sfs_cms_locale_path']), ARRAY_FILTER_USE_KEY);

            // first try to generate with Symfony's route generator
            return $this->staticRouter->generate($name, $cleanParams, $referenceType);
        } catch (RouteNotFoundException $e) {
            $cleanParams = array_filter($parameters, fn ($key) => !in_array($key, ['_locale', '_site', '_sfs_cms_locale', '_sfs_cms_site', '_sfs_cms_locale_path', 'routePath', '_route_params']), ARRAY_FILTER_USE_KEY);
            $locale = $parameters['_locale'] ?? $parameters['_sfs_cms_locale'] ?? null;
            $site = $parameters['_site'] ?? $parameters['_sfs_cms_site'] ?? null;
            $onlyChecking = isset($parameters['__twig_extra_route_defined_check']);

            // if it does not exist, try witch CMS routes
            switch ($referenceType) {
                case UrlGeneratorInterface::ABSOLUTE_URL:
                    $url = $this->urlGenerator->getUrl($name, $locale, $site, $cleanParams, $onlyChecking);
                    break;

                case UrlGeneratorInterface::ABSOLUTE_PATH:
                case UrlGeneratorInterface::RELATIVE_PATH:
                    $url = $this->urlGenerator->getPath($name, $locale, $site, $cleanParams, $onlyChecking);
                    break;

                case UrlGeneratorInterface::NETWORK_PATH:
                    $url = $this->urlGenerator->getUrl($name, $locale, $site, $cleanParams, $onlyChecking);
                    $url = preg_replace('/^(https?)/', '', $url);
                    break;

                default:
                    throw new \Exception('Invalid $referenceType');
            }

            return $url;
        }
    }

    public function match(string $pathinfo): array
    {
        return $this->staticRouter->match($pathinfo);
    }

    public function matchRequest(Request $request): array
    {
        try {
            $attributes = $this->urlMatcher->matchRequest($request);
        } catch (\Exception $e) {
            $attributes = [];
            $this->logger && $this->logger->warning(sprintf('Caught exception in CmsRouter->matchRequest: %s', $e->getMessage()));
        }

        if (isset($attributes['_controller'])) {
            return $attributes;
        }

        return $this->staticRouter->matchRequest($request) + $attributes;
    }

    public static function getSubscribedServices(): array
    {
        return Router::getSubscribedServices();
    }

    public function warmUp(string $cacheDir, string $buildDir = null): array
    {
        /** @phpstan-ignore-next-line */
        return $this->staticRouter->warmUp($cacheDir, $buildDir);
    }
}
