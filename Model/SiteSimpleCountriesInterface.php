<?php

namespace Softspring\CmsBundle\Model;

interface SiteSimpleCountriesInterface
{
    /**
     * @return array
     */
    public function getCountries(): array;

    /**
     * @param array $countries
     */
    public function setCountries(array $countries): void;
}