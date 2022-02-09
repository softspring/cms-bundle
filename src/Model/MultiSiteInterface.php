<?php

namespace Softspring\CmsBundle\Model;

use Doctrine\Common\Collections\Collection;

interface MultiSiteInterface
{
    /**
     * @return Collection|SiteInterface[]
     */
    public function getSites(): Collection;

    public function addSite(SiteInterface $site): void;

    public function removeSite(SiteInterface $site): void;
}
