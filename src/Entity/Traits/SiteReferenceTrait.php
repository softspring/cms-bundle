<?php

namespace Softspring\CmsBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Model\Traits\SiteReferenceTrait as SiteReferenceTraitModel;

trait SiteReferenceTrait
{
    use SiteReferenceTraitModel;

    /**
     * @ORM\ManyToOne(targetEntity="Softspring\CmsBundle\Model\SiteInterface")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     */
    protected ?SiteInterface $site;
}