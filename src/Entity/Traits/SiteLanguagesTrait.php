<?php

namespace Softspring\CmsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Softspring\CmsBundle\Model\Traits\SiteLanguagesTrait as SiteLanguagesTraitModel;

trait SiteLanguagesTrait
{
    use SiteLanguagesTraitModel;

    /**
     * @var array
     * @ORM\Column(name="languages", type="simple_array", nullable=false)
     */
    protected $languages = [];
}