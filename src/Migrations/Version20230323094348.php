<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230323094348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store content version media references to allow preloading and restrict deletions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cms_content_version_medias (content_version_id CHAR(36) NOT NULL, media_id CHAR(36) NOT NULL, INDEX IDX_423042FFD28591F7 (content_version_id), INDEX IDX_423042FFEA9FDD75 (media_id), PRIMARY KEY(content_version_id, media_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cms_content_version_medias ADD CONSTRAINT FK_423042FFD28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version_medias ADD CONSTRAINT FK_423042FFEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cms_content_version_medias');
    }
}
