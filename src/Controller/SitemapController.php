<?php

namespace Softspring\CmsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Router\UrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    public function generate(Request $request): Response
    {
        if (!$request->attributes->has('_sfs_cms_site')) {
            throw new NotFoundHttpException('No site was detected for sitemap');
        }
        $siteConfig = $request->attributes->get('_sfs_cms_site');

        $urls = [];

        /** @var ContentInterface $content */
        foreach ($this->em->getRepository(ContentInterface::class)->findAll() as $content) {
            if (!$content->getPublishedVersion()) {
                continue;
            }

            $seo = $content->getSeo();
            if (! ($seo['sitemap'] ?? false)) {
                continue;
            }

            if ($seo['noIndex']??false) {
                continue;
            }

            /** @var RouteInterface $contentRoute */
            foreach ($content->getRoutes() as $contentRoute) {
                foreach ($siteConfig['locales'] as $locale) {
                    $url = $this->urlGenerator->getUrl($contentRoute, $locale);

                    if ($content->getPublishedVersion()->getCreatedAt()) {
                        $lastmod = $content->getPublishedVersion()->getCreatedAt()->format('Y-m-d');
                    }

                    $urls[] = [
                        'loc' => $url,
                        'lastmod' => $lastmod ?? null,
                    ];
                }
            }
        }

        return $this->render('@SfsCms/sitemap/sitemap.xml.twig', [
            'urls' => $urls,
        ], new Response(null, 200, ['Content-type' => 'application/xml']));
    }
}