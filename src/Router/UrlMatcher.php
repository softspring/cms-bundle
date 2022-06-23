<?php

namespace Softspring\CmsBundle\Router;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Exception\SiteHasNotACanonicalHostException;
use Softspring\CmsBundle\Exception\SiteNotFoundException;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class UrlMatcher
{
    protected EntityManagerInterface $em;
    protected RouterInterface $router;
    protected UrlGenerator $urlGenerator;
    protected SiteResolver $siteResolver;

    public function __construct(EntityManagerInterface $em, RouterInterface $router, UrlGenerator $urlGenerator, SiteResolver $siteResolver)
    {
        $this->em = $em;
        $this->router = $router;
        $this->urlGenerator = $urlGenerator;
        $this->siteResolver = $siteResolver;
    }

    /**
     * @throws SiteNotFoundException
     * @throws SiteHasNotACanonicalHostException
     */
    public function matchRequest(Request $request): ?array
    {
        [$siteId, $siteConfig, $siteHostConfig] = $this->siteResolver->resolveSiteAndHost($request);

        if (!$siteId) {
            // site not found
            return [];
        }

        if ($siteConfig['https_redirect'] && 'http' === $request->getScheme()) {
            return [
                '_sfs_cms_redirect' => $this->siteResolver->getCanonicalRedirectUrl($siteConfig, $request),
                '_sfs_cms_redirect_code' => Response::HTTP_FOUND,
            ];
        }

        if ($siteHostConfig['redirect_to_canonical']) {
            return [
                '_sfs_cms_redirect' => $this->siteResolver->getCanonicalRedirectUrl($siteConfig, $request),
                '_sfs_cms_redirect_code' => Response::HTTP_FOUND,
            ];
        }

        $pathInfo = $request->getPathInfo();

        if ($siteConfig['slash_route']['enabled'] && '/' === $pathInfo) {
            switch ($siteConfig['slash_route']['behaviour']) {
                case 'redirect_to_route_with_user_language':
                    $userLocale = $request->getPreferredLanguage($siteConfig['locales']);

                    return [
                        '_sfs_cms_redirect' => $this->urlGenerator->getUrl($siteConfig['slash_route']['route'], $userLocale),
                        '_sfs_cms_redirect_code' => $siteConfig['slash_route']['redirect_code'] ?: Response::HTTP_FOUND,
                    ];

                default:
                    throw new \Exception('Not yet implemented');
            }
        }

        $attributes = [
            '_sfs_cms_site' => $siteConfig + ['id' => $siteId],
        ];

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

            switch ($route->getType()) {
                case RouteInterface::TYPE_CONTENT:
                    $attributes['_route'] = 'cms#'.$routePath->getRoute()->getId();
                    $attributes['_route_params'] = [];
                    $attributes['_controller'] = 'Softspring\CmsBundle\Controller\ContentController::renderRoutePath';
                    $attributes['routePath'] = $routePath;
                    break;

                case RouteInterface::TYPE_REDIRECT_TO_URL:
                    return [
                        '_sfs_cms_redirect' => $route->getRedirectUrl(),
                        '_sfs_cms_redirect_code' => $route->getRedirectType() ?? Response::HTTP_FOUND,
                    ];

                case RouteInterface::TYPE_REDIRECT_TO_ROUTE:
                    return [
                        '_sfs_cms_redirect' => $this->router->generate($route->getSymfonyRoute()),
                        '_sfs_cms_redirect_code' => $route->getRedirectType() ?? Response::HTTP_FOUND,
                    ];

                default:
                    throw new \Exception(sprintf('Route type %u not yet implemented', $route->getType()));
            }
        }

        return $attributes;
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

            return $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
        } catch (TableNotFoundException $e) {
            // prevent error before creating database schema
            return null;
        }
    }
}
