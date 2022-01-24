<?php

namespace Softspring\CmsBundle\Model\Traits;

trait SiteSimpleCountriesTrait
{
    /**
     * @var array
     */
    protected $countries = [];

    /**
     * @return array
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * @param array $countries
     */
    public function setCountries(array $countries): void
    {
        $this->countries = $countries;
    }
}