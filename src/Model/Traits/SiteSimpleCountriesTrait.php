<?php

namespace Softspring\CmsBundle\Model\Traits;

trait SiteSimpleCountriesTrait
{
    /**
     * @var array
     */
    protected $countries = [];

    public function getCountries(): array
    {
        return $this->countries;
    }

    public function setCountries(array $countries): void
    {
        $this->countries = $countries;
    }
}
