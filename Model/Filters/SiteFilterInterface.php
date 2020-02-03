<?php

namespace Softspring\CmsBundle\Model\Filters;

use Softspring\CmsBundle\Model\SiteInterface;

interface SiteFilterInterface
{
    /**
     * @return SiteInterface|null
     */
    public function getSite(): ?SiteInterface;
}