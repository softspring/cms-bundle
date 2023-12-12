<?php

namespace Softspring\CmsBundle\Sitemap;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Routing\UrlGenerator;

class SitemapGenerator
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected UrlGenerator $urlGenerator,
    ) {
    }

    public function sitemap(SiteInterface $site, string $sitemap): Sitemap
    {
        return new Sitemap($site, $sitemap, $this->em, $this->urlGenerator);
    }

    public function index(SiteInterface $site): Index
    {
        return new Index($site);
    }
}
