<?php

namespace Softspring\CmsBundle\DataFixtures\Purger;

use Doctrine\Bundle\FixturesBundle\Purger\PurgerFactory;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\ORM\EntityManagerInterface;

class CmsPurgerFactory implements PurgerFactory
{
    public function createForEntityManager(?string $emName, EntityManagerInterface $em, array $excluded = [], bool $purgeWithTruncate = false): PurgerInterface
    {
        $tables = $em->getConnection()->fetchAllAssociative('SHOW TABLES');

        foreach ($tables as $table) {
            $table = current($table);

            if (!str_starts_with($table, 'cms_')) {
                $excluded[] = $table;
            }
        }

        $purger = new CmsPurger($em, $excluded);
        $purger->setPurgeMode($purgeWithTruncate ? CmsPurger::PURGE_MODE_TRUNCATE : CmsPurger::PURGE_MODE_DELETE);

        return $purger;
    }
}
