<?php

namespace Softspring\CmsBundle\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Softspring\CmsBundle\Model\Filters\MultiSiteFilterInterface;
use Softspring\CmsBundle\Model\Filters\SiteFilterInterface;

class SiteFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->reflClass->implementsInterface(SiteFilterInterface::class)) {
            return $targetTableAlias.'.site_id = ' . $this->getParameter('_site');
        }

        if ($targetEntity->reflClass->implementsInterface(MultiSiteFilterInterface::class)) {
            $associationMapping = $targetEntity->getAssociationMapping('sites');
            $joinTable = $associationMapping['joinTable'];
            $joinTableName = $joinTable['name'];
            $joinTableFieldName = $joinTable['joinColumns'][0]['name'];
            $joinTableSiteFieldName = $joinTable['inverseJoinColumns'][0]['name'];
            $filterSite = $this->getParameter('_site');
            return "$targetTableAlias.id IN (SELECT $joinTableFieldName FROM $joinTableName WHERE $joinTableSiteFieldName = $filterSite )";
        }

        return '';
    }
}