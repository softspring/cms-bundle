<?php

namespace Softspring\CmsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Router\UrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class SitemapController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected Environment $twig;
    protected UrlGenerator $urlGenerator;
    protected CmsConfig $cmsConfig;

    public function __construct(EntityManagerInterface $em, Environment $twig, UrlGenerator $urlGenerator, CmsConfig $cmsConfig)
    {
        $this->em = $em;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->cmsConfig = $cmsConfig;
    }

    public function sitemap(string $site, string $sitemap): Response
    {
        $siteConfig = $this->cmsConfig->getSite($site);
        $sitemapConfig = $siteConfig['sitemaps'][$sitemap];

        $urls = [];

        /** @var ContentInterface $content */
        foreach ($this->em->getRepository(ContentInterface::class)->findBySite($site) as $content) {
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

    public function index(string $site): Response
    {
        $siteConfig = $this->cmsConfig->getSite($site);
        $hostAndProtocol = 'https://';
        foreach ($siteConfig['hosts'] as $host) {
            if ($host['canonical']) {
                $hostAndProtocol .= $host['domain'];
            }
        }

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
