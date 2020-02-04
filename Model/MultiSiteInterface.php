<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface MultiSiteInterface
{
    /**
     * @return Collection|SiteInterface[]
     */
    public function getSites(): Collection;

    /**
     * @param SiteInterface $site
     */
    public function addSite(SiteInterface $site): void;

    /**
     * @param SiteInterface $site
     */
    public function removeSite(SiteInterface $site): void;
}