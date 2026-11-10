<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Cache;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use LogicException;
use Softspring\CmsBundle\Manager\CompiledDataManagerInterface;
use Softspring\CmsBundle\Model\CompilableInterface;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\VersionInterface;
use Symfony\Component\Uid\Uuid;

/** Short cache-only transactions on the configured Doctrine connection; never flush ORM state. */
class DoctrineCompiledDataStore implements CompiledDataStoreInterface
{
    public function __construct(private EntityManagerInterface $em, private CompiledDataManagerInterface $compiledDataManager)
    {
    }

    public function find(VersionInterface $version, string $key): ?CompiledDataInterface
    {
        if (!$version instanceof CompilableInterface) {
            return null;
        }
        $entity = $this->compiledDataManager->createEntity();
        $metadata = $this->em->getClassMetadata(get_class($entity));
        $row = $this->row($metadata, $version, $key, $version->getCompiledGeneration());
        if (!$row) {
            return null;
        }
        // Hydrate a detached cache value. Domain setters must not dirty the version.
        foreach ($metadata->getFieldNames() as $field) {
            $column = $metadata->getColumnName($field);
            if (array_key_exists($column, $row)) {
                $value = Type::getType($metadata->getTypeOfField($field))->convertToPHPValue($row[$column], $this->db()->getDatabasePlatform());
                $metadata->setFieldValue($entity, $field, $value);
            }
        }
        $entity->setVersion($version);

        return $entity;
    }

    public function isTransactionActive(): bool
    {
        return $this->db()->isTransactionActive();
    }

    public function save(CompiledDataInterface $compiled, string $generation): void
    {
        $version = $compiled->getVersion();
        if (!$version instanceof CompilableInterface || !$version->getId() || null === $compiled->getKey()) {
            throw new LogicException('A compiled cache entry requires a persisted compilable version and a key.');
        }
        // A preview/publication transaction must not publish uncommitted HTML or be committed here.
        if ($this->isTransactionActive()) {
            return;
        }
        $metadata = $this->em->getClassMetadata(get_class($compiled));
        $versionMetadata = $this->em->getClassMetadata(get_class($version));
        $db = $this->db();
        for ($attempt = 0;; ++$attempt) {
            try {
                $id = $db->transactional(function () use ($db, $versionMetadata, $metadata, $compiled, $version, $generation): ?string {
                    $versionIdColumn = $versionMetadata->getColumnName($versionMetadata->getSingleIdentifierFieldName());
                    $generationColumn = $versionMetadata->getColumnName('compiledGeneration');
                    $sql = 'SELECT '.$generationColumn.' FROM '.$versionMetadata->getTableName().' WHERE '.$versionIdColumn.' = ?';
                    // DBAL 3 and 4 use different filename casing for the SQLite platform.
                    if (!is_a($db->getDatabasePlatform(), 'Doctrine\\DBAL\\Platforms\\SQLitePlatform')) {
                        $sql .= ' FOR UPDATE';
                    }
                    // Only the short cache write is serialized with editorial invalidation.
                    $current = $db->fetchOne($sql, [(string) $version->getId()]);
                    if (false === $current || (string) $current !== $generation) {
                        return null;
                    }
                    // Prefer an editorially compiled value for this generation.
                    // Reuse an obsolete row only when the current generation is absent.
                    $existing = $this->row($metadata, $version, $compiled->getKey(), $generation)
                        ?: $this->row($metadata, $version, $compiled->getKey(), null);
                    $idColumn = $metadata->getColumnName($metadata->getSingleIdentifierFieldName());
                    $id = $existing[$idColumn] ?? (string) Uuid::v7();
                    $fields = ['key' => $compiled->getKey(), 'data' => $compiled->getData(), 'errors' => $compiled->hasErrors(),
                        'createdAt' => time(), 'expiresAt' => $compiled->getExpiresAt()?->getTimestamp(), 'generation' => $generation];
                    $data = $types = [];
                    foreach ($fields as $field => $value) {
                        $column = $metadata->getColumnName($field);
                        $data[$column] = $value;
                        $types[$column] = $metadata->getTypeOfField($field);
                    }
                    $data[$metadata->getSingleAssociationJoinColumnName($this->association($version))] = (string) $version->getId();
                    $data[$idColumn] = $id;
                    if ($existing) {
                        $db->update($metadata->getTableName(), $data, [$idColumn => $id], $types);
                    } else {
                        $db->insert($metadata->getTableName(), $data, $types);
                    }

                    return $id;
                });
                break;
            } catch (RetryableException|UniqueConstraintViolationException $e) {
                // DBAL rolls back only this short transaction; the rendering EntityManager stays open.
                if ($attempt >= 2) {
                    throw $e;
                }
                usleep(20000 * ($attempt + 1));
            }
        }
        if (null !== $id) {
            $cache = $this->em->getCache();
            $cache?->evictEntity($metadata->getName(), $id);
            $cache?->evictCollection($versionMetadata->getName(), 'compiled', $version->getId());
        }
    }

    private function row(ClassMetadata $metadata, VersionInterface $version, string $key, ?string $generation): array|false
    {
        $query = $this->db()->createQueryBuilder()->select('*')->from($metadata->getTableName())
            ->where($metadata->getSingleAssociationJoinColumnName($this->association($version)).' = :version')
            ->andWhere($metadata->getColumnName('key').' = :key')
            ->setParameter('version', (string) $version->getId())->setParameter('key', $key)
            ->orderBy($metadata->getColumnName('createdAt'), 'DESC')
            ->addOrderBy($metadata->getColumnName($metadata->getSingleIdentifierFieldName()), 'DESC')
            ->setMaxResults(1);
        if (null !== $generation) {
            $query->andWhere($metadata->getColumnName('generation').' = :generation')->setParameter('generation', $generation);
        }

        return $query->executeQuery()->fetchAssociative();
    }

    private function association(VersionInterface $version): string
    {
        return $version instanceof ContentVersionInterface ? 'contentVersion' : 'sectionVersion';
    }

    protected function db(): Connection
    {
        return $this->em->getConnection();
    }
}
