<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fence invalidated compiled content and enforce one cache row per version/context';
    }

    public function up(Schema $schema): void
    {
        // Derived cache only. Run during maintenance: old duplicate entries must be rebuilt.
        $this->addSql('DELETE FROM cms_compiled_data WHERE content_version_id IS NOT NULL');
        $this->addSql("ALTER TABLE cms_content_version ADD compiled_generation VARCHAR(36) DEFAULT '' NOT NULL");
        $this->addSql("ALTER TABLE cms_compiled_data ADD generation VARCHAR(36) DEFAULT '' NOT NULL");
        $this->addSql('CREATE UNIQUE INDEX uniq_cms_content_compiled_key ON cms_compiled_data (content_version_id, compiled_key, generation)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_cms_content_compiled_key'.($this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform ? ' ON cms_compiled_data' : ''));
        $this->addSql('ALTER TABLE cms_compiled_data DROP COLUMN generation');
        $this->addSql('ALTER TABLE cms_content_version DROP COLUMN compiled_generation');
    }
}
