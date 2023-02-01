<?php

namespace Softspring\CmsBundle\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Softspring\CmsBundle\Model\SchedulableContentInterface;

class SchedulableContentFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->reflClass->implementsInterface(SchedulableContentInterface::class)) {
            return "UNIX_TIMESTAMP(NOW()) between IFNULL($targetTableAlias.publish_start_date, 0) and IFNULL($targetTableAlias.publish_end_date, 4294967295)";
        }

        return '';
    }
}
