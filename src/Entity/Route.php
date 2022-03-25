<?php

namespace Softspring\CmsBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Softspring\CmsBundle\Entity\Traits\SiteReferenceTrait;
use Softspring\CmsBundle\Model\Route as RouteModel;
use Softspring\CmsBundle\Model\SiteReferenceInterface;
use Softspring\DoctrineTemplates\Entity\Traits\KeyIdTrait;

class Route extends RouteModel // implements SiteReferenceInterface
{
//    use SiteReferenceTrait;

    protected ?string $id;
}