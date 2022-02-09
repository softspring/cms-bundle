<?php

namespace Softspring\CmsBundle\Model;

interface SiteReferenceInterface
{
    public function getSite(): ?SiteInterface;

    public function setSite(?SiteInterface $site): void;
}
