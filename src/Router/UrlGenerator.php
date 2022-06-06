<?php

namespace Softspring\CmsBundle\Router;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\Route;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class UrlGenerator
{
    protected RequestStack $requestStack;
    protected RouteManagerInterface $routeManager;
    protected ?LoggerInterface $cmsLogger;

    public function __construct(RequestStack $requestStack, RouteManagerInterface $routeManager, ?LoggerInterface $cmsLogger)
    {
        $this->requestStack = $requestStack;
        $this->routeManager = $routeManager;
        $this->cmsLogger = $cmsLogger;
    }

    /**
     * @param string|RouteInterface $routeOrName
     *
     * @throws \Exception
     */
    public function getUrl($routeOrName, ?string $locale = null): string
    {
        if ($this->isPreview()) {
            return 'javascript:confirm(\'Esto es una previsualización!\')';
        }

        $route = $routeOrName instanceof RouteInterface ? $routeOrName : $this->getRoute($routeOrName);

        if (!$route) {
            return '#';
        }

        $site = $this->getSite();

        $host = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();

        return $host.$this->getRoutePath($route, $site, $locale);
    }

    /**
     * @param string|RouteInterface $routeOrName
     *
     * @throws \Exception
     */
    public function getPath($routeOrName, ?string $locale = null): string
    {
        if ($this->isPreview()) {
            return 'javascript:confirm(\'Esto es una previsualización!\')';
        }

        $route = $routeOrName instanceof RouteInterface ? $routeOrName : $this->getRoute($routeOrName);

        if (!$route) {
            return '#';
        }

        $site = $this->getSite();

        return $this->getRoutePath($route, $site, $locale);
    }

    /**
     * @param string|RouteInterface $routeOrName
     *
     * @throws \Exception
     */
    public function getRouteAttributes($routeOrName): string
    {
        $route = $routeOrName instanceof RouteInterface ? $routeOrName : $this->getRoute($routeOrName);

        if (!$route) {
            return '';
        }

        $attrs = [];

        if ($route->getType() === RouteInterface::TYPE_CONTENT && $route->getContent()) {
            $seo = $route->getContent()->getSeo();
            if (isset($seo['noIndex'])) {
                $attrs['rel'][] = $seo['noIndex'] ? 'noindex' : 'index';
            }
            if (isset($seo['noFollow'])) {
                $attrs['rel'][] = $seo['noFollow'] ? 'nofollow' : 'follow';
            }
        }

        foreach ($attrs as $attr => $value) {
            if ($attr === 'rel' && is_array($value)) {
                $value = implode(',', $value);
            }

            $attrs[] = $attr.'="'.htmlentities($value).'"';
            unset($attrs[$attr]);
        }

        return implode(' ', $attrs);
    }

    protected function getRoutePath(Route $route, $site, ?string $locale = null): string
    {
        $locale = $locale ?: $this->requestStack->getCurrentRequest()->getLocale();

        /** @var RoutePathInterface $path */
        if ($locale) {
            $path = $route->getPaths()->filter(fn(RoutePathInterface $routePath) => $routePath->getLocale() == $locale)->first();
        }

        $path = $path ?: $route->getPaths()->first();

        return '/'.$path->getPath();
    }

    protected function getRoute($routeName): ?RouteInterface
    {
        if (!$routeName) {
            $this->cmsLogger && $this->cmsLogger->error('Empty route');

            return null;
        }

        $route = $this->routeManager->getRepository()->findOneById($routeName);

        if (!$route) {
            $this->cmsLogger && $this->cmsLogger->error(sprintf('Route %s not found', $routeName));
        }

        return $route;
    }

    protected function getSite(): ?SiteInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        /** @var SiteInterface $site */
        $site = $request->attributes->get('_site');

//        if (!$site) {
//            throw new \Exception('Can not generate route because no site is selected');
//        }

        return $site;
    }

    protected function isPreview(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->attributes->has('_cms_preview');
    }
}
