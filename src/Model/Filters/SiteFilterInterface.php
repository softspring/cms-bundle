<?php

namespace Softspring\CmsBundle\Model\Filters;

use Softspring\CmsBundle\Model\SiteInterface;

interface SiteFilterInterface
{
    public function getSite(): ?SiteInterface;
}
