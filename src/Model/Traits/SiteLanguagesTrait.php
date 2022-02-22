<?php

namespace Softspring\CmsBundle\Model\Traits;

trait SiteLanguagesTrait
{
    protected array $languages = [];

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }
}
