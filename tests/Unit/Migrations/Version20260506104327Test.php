<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Query\Query;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Softspring\CmsBundle\Migrations\Version20260506104327;

class Version20260506104327Test extends TestCase
{
    private const string DROP_COMPILED_DATA_FOREIGN_KEY = 'ALTER TABLE cms_compiled_data DROP FOREIGN KEY FK_30483E79D28591F7';
    private const string ADD_COMPILED_DATA_FOREIGN_KEY = 'ALTER TABLE cms_compiled_data ADD CONSTRAINT FK_30483E79D28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE';

    public function testDropsExistingForeignKeysBeforeNormalizingColumns(): void
    {
        $schema = $this->createCompiledDataSchema(true);
        $migration = $this->createMigration();

        $migration->up($schema);

        $statements = $this->statements($migration);
        $this->assertContains(self::DROP_COMPILED_DATA_FOREIGN_KEY, $statements);
        $this->assertContains(self::ADD_COMPILED_DATA_FOREIGN_KEY, $statements);
        $this->assertLessThan(
            array_search('ALTER TABLE cms_compiled_data CHANGE content_version_id content_version_id VARCHAR(36) DEFAULT NULL', $statements, true),
            array_search(self::DROP_COMPILED_DATA_FOREIGN_KEY, $statements, true)
        );
    }

    public function testCanResumeWhenForeignKeysWereAlreadyDropped(): void
    {
        $schema = $this->createCompiledDataSchema(false);
        $migration = $this->createMigration();

        $migration->up($schema);

        $statements = $this->statements($migration);
        $this->assertNotContains(self::DROP_COMPILED_DATA_FOREIGN_KEY, $statements);
        $this->assertContains(self::ADD_COMPILED_DATA_FOREIGN_KEY, $statements);
    }

    public function testPreservesPluginForeignKeysAroundColumnChanges(): void
    {
        foreach (['up', 'down'] as $direction) {
            $schema = $this->createCompiledDataSchema(false);
            $plugin = $schema->createTable('blog_post');
            $plugin->addColumn('id', 'string', ['length' => 36]);
            $plugin->addForeignKeyConstraint('cms_content_version', ['id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_BLOG_VERSION');
            $migration = $this->createMigration();
            $migration->$direction($schema);
            $statements = $this->statements($migration);

            $this->assertStringContainsString('DROP FOREIGN KEY FK_BLOG_VERSION', $statements[0]);
            $this->assertStringContainsString('ADD CONSTRAINT FK_BLOG_VERSION', $statements[array_key_last($statements)]);
            $this->assertStringContainsString('ON DELETE CASCADE', $statements[array_key_last($statements)]);
        }
    }

    public function testDoesNotRequireOptionalSectionsPluginTables(): void
    {
        foreach (['up', 'down'] as $direction) {
            $migration = $this->createMigration();
            $migration->$direction($this->createCompiledDataSchema(false));
            foreach ($this->statements($migration) as $statement) {
                $this->assertStringNotContainsString('ALTER TABLE cms_content_version_sections', $statement);
            }
        }
    }

    public function testPreservesSectionsForeignKeysWithoutChangingPluginColumns(): void
    {
        foreach (['up', 'down'] as $direction) {
            $schema = $this->createCompiledDataSchema(false);
            $table = $schema->createTable('cms_content_version_sections');
            $table->addColumn('content_version_id', 'string', ['length' => 36]);
            $table->addForeignKeyConstraint('cms_content_version', ['content_version_id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_4491D9C7D28591F7');
            $migration = $this->createMigration();
            $migration->$direction($schema);
            $statements = array_values(array_filter(
                $this->statements($migration),
                static fn (string $statement): bool => str_starts_with($statement, 'ALTER TABLE cms_content_version_sections ')
            ));

            self::assertCount(2, $statements);
            self::assertStringContainsString('DROP FOREIGN KEY FK_4491D9C7D28591F7', $statements[0]);
            self::assertStringContainsString('ADD CONSTRAINT FK_4491D9C7D28591F7', $statements[1]);
        }
    }

    private function createMigration(): Version20260506104327
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('createSchemaManager')->willReturn($this->createMock(AbstractSchemaManager::class));
        $connection->method('getDatabasePlatform')->willReturn(new MySQLPlatform());

        return new Version20260506104327($connection, new NullLogger());
    }

    private function createCompiledDataSchema(bool $withForeignKey): Schema
    {
        $schema = new Schema();

        $contentVersion = $schema->createTable('cms_content_version');
        $contentVersion->addColumn('id', 'string', ['length' => 36]);
        $contentVersion->setPrimaryKey(['id']);

        $compiledData = $schema->createTable('cms_compiled_data');
        $compiledData->addColumn('id', 'string', ['length' => 36]);
        $compiledData->addColumn('content_version_id', 'string', ['length' => 36, 'notnull' => false]);
        $compiledData->setPrimaryKey(['id']);

        if ($withForeignKey) {
            $compiledData->addForeignKeyConstraint(
                'cms_content_version',
                ['content_version_id'],
                ['id'],
                ['onDelete' => 'CASCADE'],
                'FK_30483E79D28591F7'
            );
        }

        return $schema;
    }

    /**
     * @return string[]
     */
    private function statements(Version20260506104327 $migration): array
    {
        return array_map(static fn (Query $query): string => $query->getStatement(), $migration->getSql());
    }
}
