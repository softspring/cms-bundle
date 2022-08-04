<?php

namespace Softspring\CmsBundle\Router;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Exception\SiteHasNotACanonicalHostException;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UrlMatcher
{
    protected EntityManagerInterface $em;
    protected UrlGenerator $urlGenerator;
    protected SiteResolver $siteResolver;

    public function __construct(EntityManagerInterface $em, UrlGenerator $urlGenerator, SiteResolver $siteResolver)
    {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->siteResolver = $siteResolver;
    }

    /**
     * @throws SiteHasNotACanonicalHostException
     */
    public function matchRequest(Request $request): ?array
    {
        if (!$request->attributes->has('_sfs_cms_site')) {
            return [];
        }

        $siteConfig = $request->attributes->get('_sfs_cms_site');
        $siteHostConfig = $request->attributes->get('_sfs_cms_site_host_config');
        $siteId = $siteConfig['id'];

        if ($siteConfig['https_redirect'] && 'http' === $request->getScheme()) {
            return $this->generateRedirect($this->siteResolver->getCanonicalRedirectUrl($siteConfig, $request), Response::HTTP_MOVED_PERMANENTLY);
        }

        if ($siteHostConfig['redirect_to_canonical']) {
            return $this->generateRedirect($this->siteResolver->getCanonicalRedirectUrl($siteConfig, $request), Response::HTTP_MOVED_PERMANENTLY);
        }

        $pathInfo = $request->getPathInfo();

        if ($siteConfig['slash_route']['enabled'] && '/' === $pathInfo) {
            switch ($siteConfig['slash_route']['behaviour']) {
                case 'redirect_to_route_with_user_language':
                    $userLocale = $request->getPreferredLanguage($siteConfig['locales']);

                    return $this->generateRedirect($this->urlGenerator->getUrl($siteConfig['slash_route']['route'], $userLocale), $siteConfig['slash_route']['redirect_code'] ?: Response::HTTP_FOUND);

                default:
                    throw new \Exception('Not yet implemented');
            }
        }

        foreach ($siteConfig['sitemaps'] as $sitemap => $sitemapConfig) {
            if ('/'.trim($sitemapConfig['url'], '/') === $pathInfo) {
                return [
                    '_controller' => 'Softspring\CmsBundle\Controller\SitemapController::sitemap',
                    'sitemap' => $sitemap,
                    'site' => $siteId,
                ];
            }
        }

        if ($siteConfig['sitemaps_index']['enabled'] && '/'.trim($siteConfig['sitemaps_index']['url'], '/') === $pathInfo) {
            return [
                '_controller' => 'Softspring\CmsBundle\Controller\SitemapController::index',
                'site' => $siteId,
            ];
        }

        $attributes = [];

        if (!empty($siteHostConfig['locale'])) {
            $attributes['_sfs_cms_locale'] = $siteHostConfig['locale'];
        }

        foreach ($siteConfig['paths'] as $path) {
            if ($path['path'] == substr($pathInfo, 0, strlen($path['path']))) {
                if ($path['locale']) {
                    if (!empty($attributes['_sfs_cms_locale'])) {
                        // TODO resolve conflict
                    }
                    $attributes['_sfs_cms_locale'] = $path['locale'];
                    $attributes['_sfs_cms_locale_path'] = $path['path'];
                    $pathInfo = substr($pathInfo, strlen($path['path']));
                }
            }
        }

        // search in database or redis-cache (TODO) ;)
        if ($routePath = $this->searchRoutePath($siteId, $pathInfo, $attributes['_sfs_cms_locale'] ?? null)) {
            $route = $routePath->getRoute();

            if ($routePath->getLocale()) {
                if (!empty($attributes['_sfs_cms_locale']) && $attributes['_sfs_cms_locale'] !== $routePath->getLocale()) {
                    // check if locale is already set by site config
                    if ($request->getLocale() && $request->getLocale() !== $routePath->getLocale()) {
                        // TODO RESOLVE LOCALE CONFLICT
                    }
                }

                $attributes['_sfs_cms_locale'] = $routePath->getLocale();
            }

            if (empty($attributes['_sfs_cms_locale_path']) && $siteConfig['locale_path_redirect_if_empty']) {
                return $this->generateRedirect($this->urlGenerator->getUrl($route->getId(), $routePath->getLocale()), $siteConfig['slash_route']['redirect_code'] ?: Response::HTTP_FOUND);
            }

            switch ($route->getType()) {
                case RouteInterface::TYPE_CONTENT:
                    $attributes['_route'] = $routePath->getRoute()->getId();
                    $attributes['_route_params'] = [];
                    $attributes['_controller'] = 'Softspring\CmsBundle\Controller\ContentController::renderRoutePath';
                    $attributes['routePath'] = $routePath;
                    break;

                case RouteInterface::TYPE_REDIRECT_TO_URL:
                    return $this->generateRedirect($route->getRedirectUrl(), $route->getRedirectType() ?? Response::HTTP_FOUND);

                case RouteInterface::TYPE_REDIRECT_TO_ROUTE:
                    return $this->generateRedirectToRoute($route->getSymfonyRoute(), $route->getRedirectType() ?? Response::HTTP_FOUND);

                default:
                    throw new \Exception(sprintf('Route type %u not yet implemented', $route->getType()));
            }
        }

        return $attributes;
    }

    protected function generateRedirect(string $url, int $statusCode): array
    {
        return [
            '_controller' => 'Softspring\CmsBundle\Controller\RedirectController::redirectToUrl',
            'url' => $url,
            'statusCode' => $statusCode,
        ];
    }

    protected function generateRedirectToRoute(string $route, int $statusCode): array
    {
        return [
            '_controller' => 'Softspring\CmsBundle\Controller\RedirectController::redirection',
            'route' => $route,
            'statusCode' => $statusCode,
        ];
    }

    protected function searchRoutePath(string $site, string $path, ?string $locale = null): ?RoutePathInterface
    {
        try {
            $qb = $this->em->getRepository(RoutePathInterface::class)->createQueryBuilder('rp');
            $qb->select('rp');
            $qb->leftJoin('rp.route', 'r');
            $qb->andWhere('rp.path = :path');
            $qb->setParameter('path', trim($path, '/'));
            $qb->andWhere('r.site = :site');
            $qb->setParameter('site', $site);

            if ($locale) {
                $qb->andWhere('rp.locale = :locale');
                $qb->setParameter('locale', $locale);
            }

            return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_OBJECT)[0] ?? null;
        } catch (TableNotFoundException $e) {
            // prevent error before creating database schema
            return null;
        }
    }
}
