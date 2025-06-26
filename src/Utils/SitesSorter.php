<?php

namespace Softspring\CmsBundle\Utils;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Softspring\CmsBundle\Model\SiteInterface;

class SitesSorter
{
    public static function sort(array|Collection $sites): ArrayCollection
    {
        $sites = $sites instanceof Collection ? $sites->toArray() : $sites;

        usort($sites, function (SiteInterface $a, SiteInterface $b) {
            return ($a->getConfig()['extra']['order'] ?? 500) <=> ($b->getConfig()['extra']['order'] ?? 500);
        });

        return new ArrayCollection($sites);
    }
}
