<?php

namespace Softspring\CmsBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RouteRepository extends EntityRepository
{
    public function getAllRouteIds(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.id')
            ->getQuery()->getSingleColumnResult();
    }
}
