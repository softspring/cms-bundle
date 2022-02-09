<?php

namespace Softspring\CmsBundle\Model;

interface SiteSimpleCountriesInterface
{
    public function getCountries(): array;

    public function setCountries(array $countries): void;
}
