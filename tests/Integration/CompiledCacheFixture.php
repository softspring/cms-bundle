<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Integration;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use ReflectionProperty;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Softspring\CmsBundle\Cache\CompiledContentCache;
use Softspring\CmsBundle\Cache\DoctrineCompiledDataStore;
use Softspring\CmsBundle\Entity\CompiledData;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Manager\CompiledDataManager;
use Softspring\CmsBundle\Model\CompiledDataInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\StoreFactory;

final class CompiledCacheFixture
{
    public static function create(string $prefix, bool $section = false, bool $locks = true, ?LoggerInterface $logger = null): array
    {
        $versionClass = $section ? 'Softspring\\CmsSectionsPlugin\\Entity\\SectionVersion' : ContentVersion::class;
        $association = $section ? 'sectionVersion' : 'contentVersion';
        $foreignKey = $section ? 'section_version_id' : 'content_version_id';
        $db = DriverManager::getConnection((new DsnParser(['mysql' => 'pdo_mysql', 'postgresql' => 'pdo_pgsql', 'sqlite' => 'pdo_sqlite']))->parse(getenv('CMS_CACHE_TEST_DATABASE_URL')));
        $config = new Configuration();
        $config->setMetadataDriverImpl(new StaticPHPDriver([]));
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('CmsCacheTestProxies');
        self::configureLazyObjects($config);
        $em = new EntityManager($db, $config);
        $reflection = new RuntimeReflectionService();
        $versionMeta = new ClassMetadata($versionClass);
        $versionMeta->initializeReflection($reflection);
        $versionMeta->setPrimaryTable(['name' => $prefix.'_version']);
        $versionMeta->mapField(['fieldName' => 'id', 'type' => 'string', 'id' => true]);
        $versionMeta->mapField(['fieldName' => 'compiledGeneration', 'columnName' => 'compiled_generation', 'type' => 'string']);
        $versionMeta->mapOneToMany(['fieldName' => 'compiled', 'targetEntity' => CompiledData::class, 'mappedBy' => $association, 'cascade' => ['persist', 'remove'], 'orphanRemoval' => true]);
        $versionMeta->wakeupReflection($reflection);
        $meta = new ClassMetadata(CompiledData::class);
        $meta->initializeReflection($reflection);
        $meta->setPrimaryTable(['name' => $prefix.'_compiled']);
        foreach (['id' => ['string', 'id'], 'key' => ['string', 'compiled_key'], 'data' => ['json', 'data'], 'errors' => ['boolean', 'errors'], 'createdAt' => ['integer', 'created_at'], 'expiresAt' => ['integer', 'expires_at'], 'generation' => ['string', 'generation']] as $field => [$type, $column]) {
            $meta->mapField(['fieldName' => $field, 'columnName' => $column, 'type' => $type, 'id' => $field === 'id', 'nullable' => $field !== 'id']);
        }
        $meta->mapManyToOne(['fieldName' => $association, 'targetEntity' => $versionClass, 'inversedBy' => 'compiled', 'joinColumns' => [['name' => $foreignKey, 'referencedColumnName' => 'id']]]);
        $meta->setIdGenerator(new AssignedGenerator());
        $meta->wakeupReflection($reflection);
        $em->getMetadataFactory()->setMetadataFor($versionClass, $versionMeta);
        $em->getMetadataFactory()->setMetadataFor(CompiledData::class, $meta);
        $em->getMetadataFactory()->setMetadataFor(CompiledDataInterface::class, $meta);
        $manager = new CompiledDataManager($em, 'test', 600);
        $store = new DoctrineCompiledDataStore($em, $manager);
        $version = new $versionClass();
        (new ReflectionProperty($versionClass, 'id'))->setValue($version, $prefix);
        $factory = $locks ? new LockFactory(StoreFactory::createStore(getenv('CMS_CACHE_TEST_REDIS_DSN'))) : null;

        return [$db, $store, new CompiledContentCache($store, $factory, $logger, 5), $version, $em];
    }

    private static function configureLazyObjects(object $configuration): void
    {
        if (method_exists($configuration, 'enableNativeLazyObjects')) {
            $configuration->enableNativeLazyObjects(true);
        }
    }
}
