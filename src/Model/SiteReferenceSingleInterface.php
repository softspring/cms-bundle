<?php

namespace Softspring\CmsBundle\Model;

interface SiteReferenceSingleInterface
{
    public function getSite(): ?SiteInterface;

    public function setSite(?SiteInterface $site): void;
}
