<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231128163442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store content last version id';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cms_content ADD last_version_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content ADD CONSTRAINT FK_A0293FB8A2C84DEF FOREIGN KEY (last_version_id) REFERENCES cms_content_version (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_A0293FB8A2C84DEF ON cms_content (last_version_id)');
        $this->addSql('UPDATE cms_content AS c SET last_version_id = (SELECT id FROM cms_content_version WHERE content_id = c.id ORDER BY created_at DESC LIMIT 1)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cms_content DROP FOREIGN KEY FK_A0293FB8A2C84DEF');
        $this->addSql('DROP INDEX IDX_A0293FB8A2C84DEF ON cms_content');
        $this->addSql('ALTER TABLE cms_content DROP last_version_id');
    }
}
