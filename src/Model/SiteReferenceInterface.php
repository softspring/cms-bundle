<?php

namespace Softspring\CmsBundle\Model;

interface SiteReferenceInterface
{
    /**
     * @return SiteInterface|null
     */
    public function getSite(): ?SiteInterface;

    /**
     * @param SiteInterface|null $site
     */
    public function setSite(?SiteInterface $site): void;
}