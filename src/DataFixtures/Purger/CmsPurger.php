<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\DataFixtures\Purger;

use Doctrine\Common\DataFixtures\Purger\ORMPurgerInterface;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\Common\DataFixtures\Sorter\TopologicalSorter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Override the default purger until https://github.com/doctrine/data-fixtures/pull/407 is merged to allow foreign keys disabling.
 * @see Doctrine\Common\DataFixtures\Purger\ORMPurger
 * @see https://github.com/doctrine/data-fixtures/pull/407/files#diff-13fb80b872ac040d79314078964971e69608f1af77f9e99bcd417cd9a65a18d2
 */
class CmsPurger implements PurgerInterface, ORMPurgerInterface
{
    public const PURGE_MODE_DELETE = 1;
    public const PURGE_MODE_TRUNCATE = 2;

    private ?EntityManagerInterface $em;

    /**
     * If the purge should be done through DELETE or TRUNCATE statements.
     */
    private int $purgeMode = self::PURGE_MODE_DELETE;

    /**
     * Table/view names to be excluded from purge.
     * @var string[]
     */
    private array $excluded;

    /**
     * @param string[] $excluded array of table/view names to be excluded from purge
     */
    public function __construct(?EntityManagerInterface $em = null, array $excluded = [])
    {
        $this->em = $em;
        $this->excluded = $excluded;
    }

    /**
     * Set the purge mode.
     */
    public function setPurgeMode(int $mode): void
    {
        $this->purgeMode = $mode;
    }

    /**
     * Get the purge mode.
     */
    public function getPurgeMode(): int
    {
        return $this->purgeMode;
    }

    public function setEntityManager(EntityManagerInterface $em): void
    {
        $this->em = $em;
    }

    /**
     * Retrieve the EntityManagerInterface instance this purger instance is using.
     */
    public function getObjectManager(): ?EntityManagerInterface
    {
        return $this->em;
    }

    public function purge(): void
    {
        $classes = [];

        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $metadata) {
            if ($metadata->isMappedSuperclass || (isset($metadata->isEmbeddedClass) && $metadata->isEmbeddedClass)) {
                continue;
            }

            $classes[] = $metadata;
        }

        $commitOrder = $this->getCommitOrder($this->em, $classes);

        // Get platform parameters
        $platform = $this->em->getConnection()->getDatabasePlatform();

        // Drop association tables first
        $orderedTables = $this->getAssociationTables($commitOrder, $platform);

        // Drop tables in reverse commit order
        for ($i = \count($commitOrder) - 1; $i >= 0; --$i) {
            $class = $commitOrder[$i];

            if (
                (isset($class->isEmbeddedClass) && $class->isEmbeddedClass)
                || $class->isMappedSuperclass
                || ($class->isInheritanceTypeSingleTable() && $class->name !== $class->rootEntityName)
            ) {
                continue;
            }

            $orderedTables[] = $this->getTableName($class, $platform);
        }

        $connection = $this->em->getConnection();
        $filterExpr = \method_exists(
            $connection->getConfiguration(),
            'getFilterSchemaAssetsExpression'
        ) ? $connection->getConfiguration()->getFilterSchemaAssetsExpression() : null;
        $emptyFilterExpression = empty($filterExpr);

        $schemaAssetsFilter = \method_exists(
            $connection->getConfiguration(),
            'getSchemaAssetsFilter'
        ) ? $connection->getConfiguration()->getSchemaAssetsFilter() : null;

        $this->disableForeignKeyChecksForMySQL($connection);

        foreach ($orderedTables as $tbl) {
            // If we have a filter expression, check it and skip if necessary
            if (!$emptyFilterExpression && !\preg_match($filterExpr, $tbl)) {
                continue;
            }

            // If the table is excluded, skip it as well
            if (false !== \array_search($tbl, $this->excluded)) {
                continue;
            }

            // Support schema asset filters as presented in
            if (\is_callable($schemaAssetsFilter) && !$schemaAssetsFilter($tbl)) {
                continue;
            }

            if (self::PURGE_MODE_DELETE === $this->purgeMode) {
                $connection->executeStatement($this->getDeleteFromTableSQL($tbl, $platform));
            } else {
                $connection->executeStatement($platform->getTruncateTableSQL($tbl, true));
            }
        }

        $this->enableForeignKeyChecksForMySQL($connection);
    }

    /**
     * @param ClassMetadata[] $classes
     *
     * @return ClassMetadata[]
     */
    private function getCommitOrder(EntityManagerInterface $em, array $classes): array
    {
        $sorter = new TopologicalSorter();

        foreach ($classes as $class) {
            if (!$sorter->hasNode($class->name)) {
                $sorter->addNode($class->name, $class);
            }

            // $class before its parents
            foreach ($class->parentClasses as $parentClass) {
                $parentClass = $em->getClassMetadata($parentClass);
                $parentClassName = $parentClass->getName();

                if (!$sorter->hasNode($parentClassName)) {
                    $sorter->addNode($parentClassName, $parentClass);
                }

                $sorter->addDependency($class->name, $parentClassName);
            }

            foreach ($class->associationMappings as $assoc) {
                if (!$assoc['isOwningSide']) {
                    continue;
                }

                $targetClass = $em->getClassMetadata($assoc['targetEntity']);
                \assert($targetClass instanceof ClassMetadata);
                $targetClassName = $targetClass->getName();

                if (!$sorter->hasNode($targetClassName)) {
                    $sorter->addNode($targetClassName, $targetClass);
                }

                // add dependency ($targetClass before $class)
                $sorter->addDependency($targetClassName, $class->name);

                // parents of $targetClass before $class, too
                foreach ($targetClass->parentClasses as $parentClass) {
                    $parentClass = $em->getClassMetadata($parentClass);
                    $parentClassName = $parentClass->getName();

                    if (!$sorter->hasNode($parentClassName)) {
                        $sorter->addNode($parentClassName, $parentClass);
                    }

                    $sorter->addDependency($parentClassName, $class->name);
                }
            }
        }

        return \array_reverse($sorter->sort());
    }

    /**
     * @param ClassMetadata[] $classes
     *
     * @return string[]
     */
    private function getAssociationTables(array $classes, AbstractPlatform $platform): array
    {
        $associationTables = [];

        foreach ($classes as $class) {
            foreach ($class->associationMappings as $assoc) {
                if (!$assoc['isOwningSide'] || ClassMetadata::MANY_TO_MANY !== $assoc['type']) {
                    continue;
                }

                $associationTables[] = $this->getJoinTableName($assoc, $class, $platform);
            }
        }

        return $associationTables;
    }

    private function getTableName(ClassMetadata $class, AbstractPlatform $platform): string
    {
        if (isset($class->table['schema']) && !\method_exists($class, 'getSchemaName')) {
            return $class->table['schema'].'.'.
                $this->em->getConfiguration()
                    ->getQuoteStrategy()
                    ->getTableName($class, $platform);
        }

        return $this->em->getConfiguration()->getQuoteStrategy()->getTableName($class, $platform);
    }

    /** @param mixed[] $assoc */
    private function getJoinTableName(
        array $assoc,
        ClassMetadata $class,
        AbstractPlatform $platform,
    ): string {
        if (isset($assoc['joinTable']['schema']) && !\method_exists($class, 'getSchemaName')) {
            return $assoc['joinTable']['schema'].'.'.
                $this->em->getConfiguration()
                    ->getQuoteStrategy()
                    ->getJoinTableName($assoc, $class, $platform);
        }

        return $this->em->getConfiguration()->getQuoteStrategy()->getJoinTableName($assoc, $class, $platform);
    }

    private function getDeleteFromTableSQL(string $tableName, AbstractPlatform $platform): string
    {
        $tableIdentifier = new Identifier($tableName);

        return 'DELETE FROM '.$tableIdentifier->getQuotedName($platform);
    }

    private function disableForeignKeyChecksForMySQL(Connection $connection): void
    {
        if (!$this->isMySQL($connection)) {
            return;
        }

        // see MySQL TRUNCATE TABLE Statement: fails for an InnoDB or NDB table
        // if there are any FOREIGN KEY constraints from other tables that
        // reference the table.
        $connection->executeStatement('SET @DOCTRINE_OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
    }

    private function enableForeignKeyChecksForMySQL(Connection $connection): void
    {
        if (!$this->isMySQL($connection)) {
            return;
        }

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = @DOCTRINE_OLD_FOREIGN_KEY_CHECKS');
    }

    private function isMySQL(Connection $connection): bool
    {
        $platform = $connection->getDatabasePlatform();

        if (!class_exists(AbstractMySQLPlatform::class)) {
            // before DBAL 3.3
            return $platform instanceof MySQLPlatform;
        }

        return $platform instanceof AbstractMySQLPlatform;
    }
}
