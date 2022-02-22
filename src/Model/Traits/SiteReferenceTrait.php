<?php

namespace Softspring\CmsBundle\Model\Traits;

use Softspring\CmsBundle\Model\SiteInterface;

trait SiteReferenceTrait
{
    protected ?SiteInterface $site = null;

    public function getSite(): ?SiteInterface
    {
        return $this->site;
    }

    public function setSite(?SiteInterface $site): void
    {
        $this->site = $site;
    }
}
