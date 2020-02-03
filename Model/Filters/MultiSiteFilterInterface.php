<?php

namespace Softspring\CmsBundle\Model\Filters;

use Doctrine\Common\Collections\Collection;
use Softspring\CmsBundle\Model\SiteInterface;

interface MultiSiteFilterInterface
{
    /**
     * @return Collection|SiteInterface[]
     */
    public function getSites(): Collection;
}