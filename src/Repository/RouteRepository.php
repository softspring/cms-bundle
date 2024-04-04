<?php

namespace Softspring\CmsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Model\RouteInterface;

class RouteRepository extends EntityRepository
{
    public function getAllRouteIds(bool $includeParents = true): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.id')
            ;

        if (!$includeParents) {
            $qb->andWhere('r.type != :parentType')
                ->setParameter('parentType', RouteInterface::TYPE_PARENT_ROUTE);
        }

        return $qb->getQuery()->getSingleColumnResult();
    }
}
