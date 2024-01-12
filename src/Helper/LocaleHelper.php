<?php

namespace Softspring\CmsBundle\Helper;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\SiteInterface;

class LocaleHelper
{
    protected CmsConfig $cmsConfig;
    protected array $enabledLocales;

    public function __construct(CmsConfig $cmsConfig, array $enabledLocales)
    {
        $this->cmsConfig = $cmsConfig;
        $this->enabledLocales = $enabledLocales;
    }

    public function getEnabledLocales(): array
    {
        return $this->enabledLocales;
    }

    /**
     * @param SiteInterface[]|null $availableSites
     */
    public function normalizeFormAvailableLocalesForSites(?array $value, ?array $availableSites): array
    {
        if (is_array($value)) {
            $availableLocales = $value;
        } elseif (!empty($availableSites)) {
            $availableLocales = [];
            foreach ($availableSites as $site) {
                $availableLocales = array_merge($availableLocales, $site->getConfig()['locales'] ?? []);
            }
        } else {
            $availableLocales = $this->enabledLocales;
        }

        $availableLocales = array_values($availableLocales);
        $availableLocales = array_unique($availableLocales);

        sort($availableLocales);

        return $availableLocales;
    }

    public function normalizeFormAvailableLocalesForContent(?array $value, ContentInterface $content): array
    {
        if (is_array($value)) {
            $availableLocales = $value;
        } else {
            $availableLocales = $content->getLocales();
        }

        $availableLocales = array_values($availableLocales);
        $availableLocales = array_unique($availableLocales);

        sort($availableLocales);

        return $availableLocales;
    }
}
