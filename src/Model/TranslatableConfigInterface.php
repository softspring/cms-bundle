<?php

namespace Softspring\CmsBundle\Model;

interface TranslatableConfigInterface
{
    public function getDefaultLocale(): ?string;

    public function setDefaultLocale(?string $defaultLocale): void;

    public function getLocales(): ?array;

    public function setLocales(?array $locales): void;

    public function addLocale(string $locale): void;
}
