<?php

namespace Softspring\CmsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Softspring\CmsBundle\Model\Traits\SiteSimpleCountriesTrait as SiteSimpleCountriesTraitModel;

/**
 * @deprecated
 */
trait SiteSimpleCountriesTrait
{
    use SiteSimpleCountriesTraitModel;

    /**
     * @ORM\Column(name="countries", type="simple_array", nullable=false)
     */
    protected array $countries = [];
}
