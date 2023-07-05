<?php

namespace Softspring\CmsBundle\Helper;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\SiteInterface;

class SiteHelper
{
    protected CmsConfig $cmsConfig;

    public function __construct(CmsConfig $cmsConfig)
    {
        $this->cmsConfig = $cmsConfig;
    }

    /**
     * @param  SiteInterface[]|null        $value
     * @param  ContentInterface|array|null $content
     * @return SiteInterface[]
     * @throws \Exception
     */
    public function normalizeFormAvailableSites(?array $value, mixed $content): array
    {
        if (is_array($value)) {
            $availableSites = $value;
        } elseif (empty($content)) {
            $availableSites = $this->cmsConfig->getSites();
        } elseif ($content instanceof ContentInterface) {
            $availableSites = $content->getSites()->toArray();
        } elseif (isset($content['_id'])) {
            $availableSites = $this->cmsConfig->getSitesForContent($content['_id']);
        } else {
            throw new \Exception('Can not get available sites');
        }

        $availableSites = array_values($availableSites);

        usort($availableSites, function (SiteInterface $a, SiteInterface $b) {
            return ($a->getConfig()['extra']['order'] ?? 500) <=> ($b->getConfig()['extra']['order'] ?? 500);
        });

        return $availableSites;
    }
}
