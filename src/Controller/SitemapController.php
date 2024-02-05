<?php

namespace Softspring\CmsBundle\Controller;

use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Sitemap\IndexFactory;
use Softspring\CmsBundle\Sitemap\InvalidSitemapException;
use Softspring\CmsBundle\Sitemap\SitemapFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends AbstractController
{
    public function __construct(
        protected SitemapFactory $sitemapFactory,
        protected IndexFactory $indexFactory,
    ) {
    }

    /**
     * @throws InvalidSitemapException
     */
    public function sitemap(SiteInterface $site, string $sitemap): Response
    {
        return $this->sitemapFactory->create($site, $sitemap)->getResponse();
    }

    public function index(SiteInterface $site): Response
    {
        return $this->indexFactory->create($site)->getResponse();
    }
}
