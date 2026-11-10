<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Cache;

use ReflectionProperty;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Cache\DoctrineCompiledDataStore;
use Softspring\CmsBundle\Entity\CompiledData;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Manager\CompiledDataManagerInterface;

class DoctrineCompiledDataStoreTest extends TestCase
{
    private string $path;
    private Connection $db;
    private EntityManagerInterface $em;
    private CompiledDataManagerInterface $manager;
    private ContentVersion $version;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('SQLite is needed for isolated cache storage tests.');
        }
        $this->path = tempnam(sys_get_temp_dir(), 'cms-compiled-');
        $this->db = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'path' => $this->path]);
        $this->db->executeStatement("CREATE TABLE cms_compiled_data (id VARCHAR(36) PRIMARY KEY, content_version_id VARCHAR(36), compiled_key VARCHAR(50), data TEXT, errors BOOLEAN, created_at INTEGER, expires_at INTEGER, generation VARCHAR(36) DEFAULT '')");
        $this->db->executeStatement('CREATE UNIQUE INDEX test_cache_context ON cms_compiled_data (content_version_id, compiled_key, generation)');
        $this->db->executeStatement("CREATE TABLE cms_content_version (id VARCHAR(36) PRIMARY KEY, compiled_generation VARCHAR(36) DEFAULT '' NOT NULL)");
        $this->db->insert('cms_content_version', ['id' => 'version-1']);
        $versionMetadata = new ClassMetadata(ContentVersion::class);
        $versionMetadata->initializeReflection(new RuntimeReflectionService());
        $versionMetadata->setPrimaryTable(['name' => 'cms_content_version']);
        $versionMetadata->mapField(['fieldName' => 'id', 'type' => 'string', 'id' => true]);
        $versionMetadata->mapField(['fieldName' => 'compiledGeneration', 'columnName' => 'compiled_generation', 'type' => 'string']);
        $metadata = new ClassMetadata(CompiledData::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->setPrimaryTable(['name' => 'cms_compiled_data']);
        foreach (['id' => ['string', 'id'], 'key' => ['string', 'compiled_key'], 'data' => ['json', 'data'], 'errors' => ['boolean', 'errors'], 'createdAt' => ['integer', 'created_at'], 'expiresAt' => ['integer', 'expires_at'], 'generation' => ['string', 'generation']] as $field => [$type, $column]) {
            $metadata->mapField(['fieldName' => $field, 'columnName' => $column, 'type' => $type, 'id' => $field === 'id', 'nullable' => $field !== 'id']);
        }
        $metadata->mapManyToOne(['fieldName' => 'contentVersion', 'targetEntity' => ContentVersion::class, 'joinColumns' => [['name' => 'content_version_id', 'referencedColumnName' => 'id']]]);
        $metadata->wakeupReflection(new RuntimeReflectionService());
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('getConnection')->willReturn($this->db);
        $this->em->method('getClassMetadata')->willReturnCallback(fn ($class): ClassMetadata => $class === CompiledData::class ? $metadata : $versionMetadata);
        $this->em->expects(self::never())->method('flush');
        $this->em->expects(self::never())->method('persist');
        $this->manager = $this->createMock(CompiledDataManagerInterface::class);
        $this->manager->method('createEntity')->willReturnCallback(fn (): CompiledData => new CompiledData());
        $this->version = new ContentVersion();
        (new ReflectionProperty(ContentVersion::class, 'id'))->setValue($this->version, 'version-1');
    }

    protected function tearDown(): void
    {
        if (isset($this->db)) {
            $this->db->close();
            unlink($this->path);
        }
    }

    private function value(string $html): CompiledData
    {
        $value = new CompiledData();
        $value->setVersion($this->version);
        $value->setKey('site/en');
        $value->setDataPart('content', $html);
        $value->setExpiresAt(new DateTime('+1 hour'));

        return $value;
    }

    public function testCacheOnlyWritesReplaceOneRowWithoutOrmWrites(): void
    {
        $first = new DoctrineCompiledDataStore($this->em, $this->manager);
        $second = new DoctrineCompiledDataStore($this->em, $this->manager);
        $first->save($this->value('Hello'), '');
        $second->save($this->value('Updated'), '');
        self::assertSame(1, (int) $this->db->fetchOne('SELECT COUNT(*) FROM cms_compiled_data'));
        $cached = $first->find($this->version, 'site/en');
        self::assertSame('Updated', $cached->getDataPart('content'));
        self::assertFalse($cached->hasErrors());
        self::assertSame($this->version, $cached->getVersion());
        self::assertNotNull($cached->getExpiresAt());
        self::assertCount(0, $this->version->getCompiled());
    }

    public function testLegacyRandomIdentifierIsUpdatedRatherThanDuplicated(): void
    {
        $this->db->insert('cms_compiled_data', ['id' => 'legacy-id', 'content_version_id' => 'version-1', 'compiled_key' => 'site/en', 'data' => '{"content":"Old"}', 'errors' => 0, 'created_at' => 1]);
        $store = new DoctrineCompiledDataStore($this->em, $this->manager);
        $store->save($this->value('New'), '');
        self::assertSame(1, (int) $this->db->fetchOne('SELECT COUNT(*) FROM cms_compiled_data'));
        self::assertSame('legacy-id', $this->db->fetchOne('SELECT id FROM cms_compiled_data'));
        self::assertSame('New', $store->find($this->version, 'site/en')->getDataPart('content'));
    }
    public function testInvalidationRejectsLateWritesAndOldGenerationReads(): void
    {
        $store = new DoctrineCompiledDataStore($this->em, $this->manager);
        $store->save($this->value('Before invalidation'), '');
        $this->version->cleanCompiled();
        $generation = $this->version->getCompiledGeneration();
        $this->db->update('cms_content_version', ['compiled_generation' => $generation], ['id' => 'version-1']);
        $store->save($this->value('Late stale render'), '');
        self::assertNull($store->find($this->version, 'site/en'));
        $store->save($this->value('Fresh render'), $generation);
        self::assertSame('Fresh render', $store->find($this->version, 'site/en')->getDataPart('content'));
        self::assertSame(1, (int) $this->db->fetchOne('SELECT COUNT(*) FROM cms_compiled_data'));
    }

    public function testEditorialTransactionIsNeitherCommittedNorUsedForSharedCache(): void
    {
        $store = new DoctrineCompiledDataStore($this->em, $this->manager);
        $this->db->beginTransaction();
        $store->save($this->value('Uncommitted'), '');
        self::assertTrue($this->db->isTransactionActive());
        $this->db->rollBack();
        self::assertSame(0, (int) $this->db->fetchOne('SELECT COUNT(*) FROM cms_compiled_data'));
    }

}
