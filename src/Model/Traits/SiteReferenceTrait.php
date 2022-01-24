<?php

namespace Softspring\CmsBundle\Model\Traits;

use Softspring\CmsBundle\Model\SiteInterface;

trait SiteReferenceTrait
{
    protected ?SiteInterface $site;

    /**
     * @return SiteInterface|null
     */
    public function getSite(): ?SiteInterface
    {
        return $this->site;
    }

    /**
     * @param SiteInterface|null $site
     */
    public function setSite(?SiteInterface $site): void
    {
        $this->site = $site;
    }
}