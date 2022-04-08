<?php

namespace Softspring\CmsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Softspring\CmsBundle\Model\Traits\SiteLanguagesTrait as SiteLanguagesTraitModel;

/**
 * @deprecated
 */
trait SiteLanguagesTrait
{
    use SiteLanguagesTraitModel;

    /**
     * @ORM\Column(name="languages", type="simple_array", nullable=false)
     */
    protected array $languages = [];
}
