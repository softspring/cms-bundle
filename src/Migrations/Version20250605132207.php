<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250605132207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create compiled data index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX expires_at_idx ON cms_compiled_data (expires_at)');
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->addSql('DROP INDEX expires_at_idx');

            return;
        }

        $this->addSql('DROP INDEX expires_at_idx ON cms_compiled_data');
    }
}
