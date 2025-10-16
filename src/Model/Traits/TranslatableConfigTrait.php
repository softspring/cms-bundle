<?php

namespace Softspring\CmsBundle\Model\Traits;

trait TranslatableConfigTrait
{
    protected ?string $defaultLocale = null;

    protected ?array $locales = null;

    public function getDefaultLocale(): ?string
    {
        return $this->defaultLocale;
    }

    public function setDefaultLocale(?string $defaultLocale): void
    {
        $this->defaultLocale = $defaultLocale;
        $this->addLocale($defaultLocale);
    }

    public function getLocales(): ?array
    {
        return array_unique(array_merge($this->defaultLocale ? [$this->defaultLocale] : [], $this->locales ?? []));
    }

    public function setLocales(?array $locales): void
    {
        $this->locales = $locales;
        $this->defaultLocale && $this->addLocale($this->defaultLocale);
    }

    public function addLocale(string $locale): void
    {
        $this->locales = array_unique(array_merge($this->getLocales(), [$locale]));
    }
}
