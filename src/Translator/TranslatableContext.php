<?php

namespace Softspring\CmsBundle\Translator;

class TranslatableContext
{
    protected ?array $locales = null;

    public function __construct(protected string $defaultLocale, protected array $enabledLocales)
    {
        $this->enabledLocales = $this->enabledLocales ?: [$this->defaultLocale];
        $this->locales = $this->enabledLocales;
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    public function getEnabledLocales(): array
    {
        return $this->enabledLocales;
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    public function setDefaultLocale(string $defaultLocale): void
    {
        $this->defaultLocale = $defaultLocale;
    }
}
