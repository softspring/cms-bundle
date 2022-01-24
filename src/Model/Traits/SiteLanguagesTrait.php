<?php

namespace Softspring\CmsBundle\Model\Traits;

use Doctrine\ORM\Mapping as ORM;

trait SiteLanguagesTrait
{
    /**
     * @var array
     */
    protected $languages = [];

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @param array $languages
     */
    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }
}