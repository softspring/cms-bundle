<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230323095954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store content version route references to allow preloading and restrict deletions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cms_content_version_routes (content_version_id CHAR(36) NOT NULL, route_id VARCHAR(255) NOT NULL, INDEX IDX_62372FCDD28591F7 (content_version_id), INDEX IDX_62372FCD34ECB4E6 (route_id), PRIMARY KEY(content_version_id, route_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cms_content_version_routes ADD CONSTRAINT FK_62372FCDD28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version_routes ADD CONSTRAINT FK_62372FCD34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cms_content_version_routes');
    }
}
