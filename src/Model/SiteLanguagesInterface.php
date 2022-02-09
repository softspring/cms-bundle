<?php

namespace Softspring\CmsBundle\Model;

interface SiteLanguagesInterface
{
    public function getLanguages(): array;

    public function setLanguages(array $languages): void;
}
