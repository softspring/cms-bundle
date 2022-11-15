<?php

namespace Softspring\CmsBundle\Routing;

use Softspring\CmsBundle\Router\UrlGenerator;
use Softspring\CmsBundle\Router\UrlMatcher;
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

    public function __construct(Router $staticRouter, UrlMatcher $urlMatcher, UrlGenerator $urlGenerator)
    {
        $this->staticRouter = $staticRouter;
        $this->urlMatcher = $urlMatcher;
        $this->urlGenerator = $urlGenerator;
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
            // first try to generate with Symfony's route generator
            return $this->staticRouter->generate($name, $parameters, $referenceType);
        } catch (RouteNotFoundException $e) {
            // if it does not exist, try witch CMS routes
            switch ($referenceType) {
                case UrlGeneratorInterface::ABSOLUTE_URL:
                    $url = $this->urlGenerator->getUrl($name, $parameters['_locale'] ?? '');
                    break;

                case UrlGeneratorInterface::ABSOLUTE_PATH:
                case UrlGeneratorInterface::RELATIVE_PATH:
                    $url = $this->urlGenerator->getPath($name, $parameters['_locale'] ?? '');
                    break;

                case UrlGeneratorInterface::NETWORK_PATH:
                    $url = $this->urlGenerator->getUrl($name, $parameters['_locale'] ?? '');
                    $url = preg_replace('/^(https?)', '', $url);
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
        $attributes = $this->urlMatcher->matchRequest($request);

        if (isset($attributes['_controller'])) {
            return $attributes;
        }

        return $this->staticRouter->matchRequest($request) + $attributes;
    }

    public static function getSubscribedServices(): array
    {
        return Router::getSubscribedServices();
    }

    public function warmUp(string $cacheDir)
    {
        $this->staticRouter->warmUp($cacheDir);
    }
}
