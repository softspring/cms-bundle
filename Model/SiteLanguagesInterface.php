<?php

namespace Softspring\CmsBundle\Model;

interface SiteLanguagesInterface
{
    /**
     * @return array
     */
    public function getLanguages(): array;

    /**
     * @param array $languages
     */
    public function setLanguages(array $languages): void;
}