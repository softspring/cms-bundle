<?php

namespace Softspring\CmsBundle\Helper;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Translator\TranslatableContext;

class LocaleHelper
{
    public function __construct(protected CmsConfig $cmsConfig, protected TranslatableContext $translatableContext)
    {
    }

    public function getEnabledLocales(): array
    {
        return $this->translatableContext->getEnabledLocales();
    }

    public function getDefaultLocale(): string
    {
        return $this->translatableContext->getDefaultLocale();
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
            $availableLocales = $this->getEnabledLocales();
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
