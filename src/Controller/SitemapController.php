<?php

namespace Softspring\CmsBundle\Controller;

use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Sitemap\SitemapGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends AbstractController
{
    public function __construct(
        protected SitemapGenerator $sitemapGenerator
    ) {
    }

    public function sitemap(SiteInterface $site, string $sitemap): Response
    {
        return $this->sitemapGenerator->sitemap($site, $sitemap)->getResponse();
    }

    public function index(SiteInterface $site): Response
    {
        return $this->sitemapGenerator->index($site)->getResponse();
    }
}
