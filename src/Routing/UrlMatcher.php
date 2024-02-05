<?php

namespace Softspring\CmsBundle\Routing;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Exception\SiteHasNotACanonicalHostException;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
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
     * @throws \Exception
     */
    public function matchRequest(Request $request): ?array
    {
        if (!$request->attributes->has('_sfs_cms_site')) {
            return [];
        }

        /** @var SiteInterface $site */
        $site = $request->attributes->get('_sfs_cms_site');
        $siteConfig = $site->getConfig();
        $siteHostConfig = $request->attributes->get('_sfs_cms_site_host_config');

        if ($siteConfig['https_redirect'] && 'http' === $request->getScheme()) {
            return $this->generateRedirect($this->siteResolver->getCanonicalRedirectUrl($site, $request), Response::HTTP_PERMANENTLY_REDIRECT);
        }

        if ($siteHostConfig['redirect_to_canonical']) {
            return $this->generateRedirect($this->siteResolver->getCanonicalRedirectUrl($site, $request), Response::HTTP_PERMANENTLY_REDIRECT);
        }

        $pathInfo = $request->getPathInfo();
        $pathInfoHasTrailingSlash = str_ends_with($pathInfo, '/');

        if ($siteConfig['slash_route']['enabled'] && '/' === $pathInfo) {
            switch ($siteConfig['slash_route']['behaviour']) {
                case 'redirect_to_route_with_user_language':
                    $userLocale = $request->getPreferredLanguage($siteConfig['locales']);

                    return $this->generateRedirect($this->urlGenerator->getUrl($siteConfig['slash_route']['route'], $userLocale), $siteConfig['slash_route']['redirect_code'] ?: Response::HTTP_TEMPORARY_REDIRECT);

                default:
                    throw new \Exception('Not yet implemented');
            }
        }

        foreach ($siteConfig['sitemaps'] as $sitemap => $sitemapConfig) {
            if ('/'.trim($sitemapConfig['url'], '/') === $pathInfo) {
                return [
                    '_controller' => 'Softspring\CmsBundle\Controller\SitemapController::sitemap',
                    'sitemap' => $sitemap,
                    'site' => $site,
                ];
            }
        }

        if ($siteConfig['sitemaps_index']['enabled'] && '/'.trim($siteConfig['sitemaps_index']['url'], '/') === $pathInfo) {
            return [
                '_controller' => 'Softspring\CmsBundle\Controller\SitemapController::index',
                'site' => $site,
            ];
        }

        $attributes = [];

        if (!empty($siteHostConfig['locale'])) {
            $attributes['_sfs_cms_locale'] = $siteHostConfig['locale'];
        }

        foreach ($siteConfig['paths'] as $path) {
            if (str_starts_with($pathInfo, $path['path'])) {
                if ($path['locale']) {
                    if (!empty($attributes['_sfs_cms_locale'])) {
                        // TODO resolve conflict
                    }
                    $attributes['_sfs_cms_locale'] = $path['locale'];
                    $attributes['_sfs_cms_locale_path'] = $path['path'];

                    $pathInfo = substr($pathInfo, strlen($path['path']));

                    if ($path['trailing_slash_on_root'] && '' === $pathInfo && !$pathInfoHasTrailingSlash) {
                        $url = parse_url($request->getUri());
                        $url = sprintf('%s://%s%s', $url['scheme'], $url['host'], $url['path'].'/');

                        return $this->generateRedirect($url, Response::HTTP_PERMANENTLY_REDIRECT);
                    }
                }
            }
        }

        // search in database or redis-cache (TODO) ;)
        if ($routePath = $this->searchRoutePath($site, $pathInfo, $attributes['_sfs_cms_locale'] ?? null)) {
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
                return $this->generateRedirect($this->urlGenerator->getUrl($route->getId(), $routePath->getLocale()), $siteConfig['slash_route']['redirect_code'] ?: Response::HTTP_TEMPORARY_REDIRECT);
            }

            //            if ($attributes['_sfs_cms_locale_path']) {
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
            //            }
        }

        if (isset($attributes['_sfs_cms_locale'])) {
            $attributes['_locale'] = $attributes['_sfs_cms_locale'];
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

    protected function generateRedirectToRoute(array $route, int $statusCode): array
    {
        return [
            '_controller' => 'Softspring\CmsBundle\Controller\RedirectController::redirection',
            'route' => $route['route_name'],
            'routeParams' => $route['route_params'],
            'statusCode' => $statusCode,
        ];
    }

    protected function searchRoutePath(SiteInterface $site, string $path, ?string $locale = null): ?RoutePathInterface
    {
        try {
            $qb = $this->em->getRepository(RoutePathInterface::class)->createQueryBuilder('rp');
            $qb->select('rp');
            $qb->leftJoin('rp.route', 'r');
            $qb->leftJoin('rp.sites', 's');
            $qb->andWhere('rp.compiledPath = :path');
            $qb->setParameter('path', trim($path, '/'));
            $qb->andWhere('s = :site');
            $qb->andWhere('r.type != 4');
            $qb->setParameter('site', $site);

            if ($locale) {
                $qb->andWhere('rp.locale = :locale');
                $qb->setParameter('locale', $locale);
            }

            return $qb->getQuery()->setCacheable(true)->setResultCacheLifetime(60)->getResult(AbstractQuery::HYDRATE_OBJECT)[0] ?? null;
        } catch (TableNotFoundException $e) {
            // prevent error before creating database schema
            return null;
        }
    }
}
