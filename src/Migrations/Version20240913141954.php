<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240913141954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add compiled data table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cms_compiled_data (id CHAR(36) NOT NULL, content_version_id CHAR(36) DEFAULT NULL, compiled_key VARCHAR(50) DEFAULT NULL, created_at INT UNSIGNED DEFAULT NULL, expires_at INT UNSIGNED DEFAULT NULL, data JSON DEFAULT NULL, INDEX IDX_30483E79D28591F7 (content_version_id), INDEX key_idx (compiled_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cms_compiled_data ADD CONSTRAINT FK_30483E79D28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version DROP compiled_modules, DROP compiled');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_compiled_data DROP FOREIGN KEY FK_30483E79D28591F7');
        $this->addSql('DROP TABLE cms_compiled_data');
        $this->addSql('ALTER TABLE cms_content_version ADD compiled_modules JSON DEFAULT NULL, ADD compiled JSON DEFAULT NULL');
    }
}
