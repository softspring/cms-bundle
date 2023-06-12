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
                    }

                    $urls[] = $url;
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
        $hostAndProtocol = ($site->getCanonicalScheme()??'https') .'://' . $site->getCanonicalHost();

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
