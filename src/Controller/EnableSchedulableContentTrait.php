<?php

namespace Softspring\CmsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Doctrine\Filter\SchedulableContentFilter;

trait EnableSchedulableContentTrait
{
    protected EntityManagerInterface $em;

    protected function enableSchedulableFilter(): void
    {
        $this->em->getConfiguration()->addFilter('schedulable', SchedulableContentFilter::class);
        $this->em->getFilters()->enable('schedulable');
    }
}