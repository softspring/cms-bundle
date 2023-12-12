<?php

namespace Softspring\CmsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class SitemapController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected Environment $twig;
    protected UrlGenerator $urlGenerator;

    public function __construct(EntityManagerInterface $em, Environment $twig, UrlGenerator $urlGenerator)
    {
        $this->em = $em;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    public function sitemap(SiteInterface $site, string $sitemap): Response
    {
        $siteConfig = $site->getConfig();
        $sitemapConfig = $siteConfig['sitemaps'][$sitemap];

        $urls = [];

        $qb = $this->em->getRepository(ContentInterface::class)->createQueryBuilder('c');
        $qb->leftJoin('c.sites', 's');
        $qb->andWhere('s = :site')->setParameter('site', $site);

        /** @var ContentInterface $content */
        foreach ($qb->getQuery()->getResult() as $content) {
            if (!$content->getPublishedVersion()) {
                continue;
            }

            $seo = $content->getSeo();
            if (!($seo['sitemap'] ?? false)) {
                continue;
            }

            // TODO check sitemap name

            if ($seo['noIndex'] ?? false) {
                continue;
            }

            /** @var RouteInterface $contentRoute */
            foreach ($content->getRoutes() as $contentRoute) {
                foreach ($siteConfig['locales'] as $locale) {
                    // test eligibility for inclusion in this site's sitemap, for this locale
                    if ($this->urlGenerator->testRouteForSitemapEligibility($contentRoute, $locale, $site)) {
                        // the loc should be added, continue as normal
                        $url = ['loc' => $this->urlGenerator->getUrl($contentRoute, $locale)];

                        if ($content->getPublishedVersion()->getCreatedAt()) {
                            $url['lastmod'] = $content->getPublishedVersion()->getCreatedAt()->format('Y-m-d');
                        }

                        if ($seo['sitemapChangefreq'] ?? false) {
                            $url['changefreq'] = $seo['sitemapChangefreq'];
                        } elseif ($sitemapConfig['default_changefreq'] ?? false) {
                            $url['changefreq'] = $sitemapConfig['default_changefreq'];
                        }

                        if ($seo['sitemapPriority'] ?? false) {
                            $url['priority'] = $seo['sitemapPriority'];
                        } elseif ($sitemapConfig['default_priority'] ?? false) {
                            $url['priority'] = $sitemapConfig['default_priority'];
                        }

                        if ($sitemapConfig['alternates']) {
                            $url['alternates'] = [];
                            // all alternates, including self path
                            // @see https://developers.google.com/search/docs/advanced/crawling/localized-versions?hl=es#sitemap
                            foreach ($contentRoute->getPaths() as $path) {
                                $url['alternates'][] = [
                                    'locale' => $path->getLocale(),
                                    'url' => $this->urlGenerator->getUrl($contentRoute, $path->getLocale(), $site),
                                ];
                            }

                            // routes for content that are available in other sites should also be included
                            // check if the content route is available in other sites
                            $otherSites = $contentRoute->getSites()->filter(function (SiteInterface $otherSite) use ($site) {
                                return $otherSite->getId() !== $site->getId();
                            });

                            // for all the other sites where the content route exists
                            /** @var SiteInterface $otherSite */
                            foreach ($otherSites as $otherSite) {
                                // loop on the route paths once more
                                foreach ($contentRoute->getPaths() as $path) {
                                    // check if this path is available in the other site
                                    if ($this->urlGenerator->testRouteForSitemapEligibility($contentRoute, $path->getLocale(), $otherSite)) {
                                        // if it is, add it to the alternates
                                        $url['alternates'][] = [
                                            'locale' => $path->getLocale(),
                                            'url' => $this->urlGenerator->getUrl($contentRoute, $path->getLocale(), $otherSite),
                                        ];
                                    }
                                }
                            }
                        }
                        // push back to the full array of urls
                        $urls[] = $url;
                    }
                }
            }
        }

        $response = new Response(null, 200, ['Content-type' => 'application/xml']);

        if ($sitemapConfig['cache_ttl'] ?? false) {
            $response->setPublic();
            $response->setMaxAge($sitemapConfig['cache_ttl']);
        }

        return $this->render('@SfsCms/sitemap/sitemap.xml.twig', ['urls' => $urls], $response);
    }

    public function index(SiteInterface $site): Response
    {
        $siteConfig = $site->getConfig();
        $hostAndProtocol = ($site->getCanonicalScheme() ?? 'https').'://'.$site->getCanonicalHost();

        $sitemaps = [];

        foreach ($siteConfig['sitemaps'] as $sitemap => $sitemapConfig) {
            $sitemaps[] = [
                'loc' => "$hostAndProtocol/{$sitemapConfig['url']}",
            ];
        }

        $response = new Response(null, 200, ['Content-type' => 'application/xml']);

        //        if ($sitemapConfig['cache_ttl'] ?? false) {
        //            $response->setPublic();
        //            $response->setMaxAge($sitemapConfig['cache_ttl']);
        //        }

        return $this->render('@SfsCms/sitemap/index.xml.twig', ['sitemaps' => $sitemaps], $response);
    }
}
