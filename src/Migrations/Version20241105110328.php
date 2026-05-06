<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241105110328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set up route ids to be 100 chars long';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->addSql('ALTER TABLE cms_content_version_routes DROP CONSTRAINT FK_62372FCD34ECB4E6');
            $this->addSql('ALTER TABLE cms_route_path DROP CONSTRAINT FK_95D1F10F34ECB4E6');
            $this->addSql('ALTER TABLE cms_route DROP CONSTRAINT FK_2CB7BB55727ACA70');
            $this->addSql('ALTER TABLE cms_route_sites DROP CONSTRAINT FK_BC9E2F1934ECB4E6');

            $this->addSql('ALTER TABLE cms_content_version_routes ALTER COLUMN route_id TYPE VARCHAR(100)');
            $this->addSql('ALTER TABLE cms_route ALTER COLUMN id TYPE VARCHAR(100)');
            $this->addSql('ALTER TABLE cms_route ALTER COLUMN parent_id TYPE VARCHAR(100)');
            $this->addSql('ALTER TABLE cms_route_sites ALTER COLUMN route_id TYPE VARCHAR(100)');
            $this->addSql('ALTER TABLE cms_route_path ALTER COLUMN route_id TYPE VARCHAR(100)');

            $this->addSql('ALTER TABLE cms_content_version_routes ADD CONSTRAINT FK_62372FCD34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE RESTRICT');
            $this->addSql('ALTER TABLE cms_route_path ADD CONSTRAINT FK_95D1F10F34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE cms_route_sites ADD CONSTRAINT FK_BC9E2F1934ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE cms_route ADD CONSTRAINT FK_2CB7BB55727ACA70 FOREIGN KEY (parent_id) REFERENCES cms_route (id) ON DELETE RESTRICT');

            return;
        }

        $this->addSql('ALTER TABLE cms_content_version_routes DROP FOREIGN KEY FK_62372FCD34ECB4E6');
        $this->addSql('ALTER TABLE cms_route_path DROP FOREIGN KEY FK_95D1F10F34ECB4E6');
        $this->addSql('ALTER TABLE cms_route DROP FOREIGN KEY FK_2CB7BB55727ACA70');
        $this->addSql('ALTER TABLE cms_route_sites DROP FOREIGN KEY FK_BC9E2F1934ECB4E6');

        $this->addSql('ALTER TABLE cms_content_version_routes CHANGE route_id route_id VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE cms_route CHANGE id id VARCHAR(100) NOT NULL, CHANGE parent_id parent_id VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route_sites CHANGE route_id route_id VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE cms_route_path CHANGE route_id route_id VARCHAR(100) DEFAULT NULL');

        $this->addSql('ALTER TABLE cms_content_version_routes ADD CONSTRAINT FK_62372FCD34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE cms_route_path ADD CONSTRAINT FK_95D1F10F34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route_sites ADD CONSTRAINT FK_BC9E2F1934ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route ADD CONSTRAINT FK_2CB7BB55727ACA70 FOREIGN KEY (parent_id) REFERENCES cms_route (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->addSql('ALTER TABLE cms_content_version_routes DROP CONSTRAINT FK_62372FCD34ECB4E6');
            $this->addSql('ALTER TABLE cms_route_path DROP CONSTRAINT FK_95D1F10F34ECB4E6');
            $this->addSql('ALTER TABLE cms_route DROP CONSTRAINT FK_2CB7BB55727ACA70');
            $this->addSql('ALTER TABLE cms_route_sites DROP CONSTRAINT FK_BC9E2F1934ECB4E6');

            $this->addSql('ALTER TABLE cms_content_version_routes ALTER COLUMN route_id TYPE VARCHAR(13)');
            $this->addSql('ALTER TABLE cms_route ALTER COLUMN id TYPE VARCHAR(13)');
            $this->addSql('ALTER TABLE cms_route ALTER COLUMN parent_id TYPE VARCHAR(13)');
            $this->addSql('ALTER TABLE cms_route_path ALTER COLUMN route_id TYPE VARCHAR(13)');
            $this->addSql('ALTER TABLE cms_route_sites ALTER COLUMN route_id TYPE VARCHAR(13)');

            $this->addSql('ALTER TABLE cms_content_version_routes ADD CONSTRAINT FK_62372FCD34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE RESTRICT');
            $this->addSql('ALTER TABLE cms_route_path ADD CONSTRAINT FK_95D1F10F34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE cms_route_sites ADD CONSTRAINT FK_BC9E2F1934ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE cms_route ADD CONSTRAINT FK_2CB7BB55727ACA70 FOREIGN KEY (parent_id) REFERENCES cms_route (id) ON DELETE RESTRICT');

            return;
        }

        $this->addSql('ALTER TABLE cms_content_version_routes DROP FOREIGN KEY FK_62372FCD34ECB4E6');
        $this->addSql('ALTER TABLE cms_route_path DROP FOREIGN KEY FK_95D1F10F34ECB4E6');
        $this->addSql('ALTER TABLE cms_route DROP FOREIGN KEY FK_2CB7BB55727ACA70');
        $this->addSql('ALTER TABLE cms_route_sites DROP FOREIGN KEY FK_BC9E2F1934ECB4E6');

        $this->addSql('ALTER TABLE cms_content_version_routes CHANGE route_id route_id VARCHAR(13) NOT NULL');
        $this->addSql('ALTER TABLE cms_route CHANGE id id VARCHAR(13) NOT NULL, CHANGE parent_id parent_id VARCHAR(13) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route_path CHANGE route_id route_id VARCHAR(13) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route_sites CHANGE route_id route_id VARCHAR(13) NOT NULL');

        $this->addSql('ALTER TABLE cms_content_version_routes ADD CONSTRAINT FK_62372FCD34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE cms_route_path ADD CONSTRAINT FK_95D1F10F34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route_sites ADD CONSTRAINT FK_BC9E2F1934ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route ADD CONSTRAINT FK_2CB7BB55727ACA70 FOREIGN KEY (parent_id) REFERENCES cms_route (id) ON DELETE RESTRICT');
    }
}
