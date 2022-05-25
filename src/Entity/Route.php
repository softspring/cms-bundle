<?php

namespace Softspring\CmsBundle\Entity;

use Softspring\CmsBundle\Entity\Traits\SiteReferenceTrait;
use Softspring\CmsBundle\Model\Route as RouteModel;
use Softspring\CmsBundle\Model\SiteReferenceInterface;

class Route extends RouteModel // implements SiteReferenceInterface
{
//    use SiteReferenceTrait;

    protected ?string $id;
}
