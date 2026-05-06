<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260506104327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize CMS column types for MySQL and PostgreSQL';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->addSql('ALTER TABLE cms_block ALTER COLUMN id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_compiled_data ALTER COLUMN id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_compiled_data ALTER COLUMN content_version_id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_content ALTER COLUMN id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_content ALTER COLUMN published_version_id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_content ALTER COLUMN last_version_id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_content_sites ALTER COLUMN content_id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_content_page ALTER COLUMN id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_content_version ALTER COLUMN id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_content_version ALTER COLUMN content_id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_content_version_medias ALTER COLUMN content_version_id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_content_version_routes ALTER COLUMN content_version_id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_menu ALTER COLUMN id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_menu ALTER COLUMN menu_type TYPE VARCHAR(30)');
            $this->addSql('ALTER TABLE cms_menu_item ALTER COLUMN id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_menu_item ALTER COLUMN menu_id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_menu_item ALTER COLUMN parent_item_id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_route ALTER COLUMN content_id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_route_path ALTER COLUMN id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE cms_route_path ALTER COLUMN path TYPE VARCHAR(100)');
            $this->addSql('ALTER TABLE cms_route_path ALTER COLUMN locale TYPE VARCHAR(5)');
            $this->addSql('ALTER TABLE cms_route_path ALTER COLUMN compiled_path TYPE VARCHAR(255)');
            $this->addSql('ALTER TABLE cms_route_path_sites ALTER COLUMN route_path_id TYPE VARCHAR(36)');
            $this->addSql('ALTER TABLE media ALTER COLUMN type_private SET DEFAULT false');
            $this->addSql('ALTER TABLE media ALTER COLUMN type_private DROP NOT NULL');
            $this->addSql('ALTER TABLE media ALTER COLUMN sha1 TYPE VARCHAR(40)');

            return;
        }

        $this->addSql('ALTER TABLE cms_block MODIFY id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_compiled_data MODIFY id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_compiled_data MODIFY content_version_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content MODIFY id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content MODIFY published_version_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content MODIFY last_version_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_sites MODIFY content_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_page MODIFY id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_version MODIFY id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_version MODIFY content_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_version_medias MODIFY content_version_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_version_routes MODIFY content_version_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_menu MODIFY id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_menu MODIFY menu_type VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE cms_menu_item MODIFY id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_menu_item MODIFY menu_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_menu_item MODIFY parent_item_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route MODIFY content_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route_path MODIFY id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_route_path MODIFY path VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE cms_route_path MODIFY locale VARCHAR(5) NOT NULL');
        $this->addSql('ALTER TABLE cms_route_path MODIFY compiled_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route_path_sites MODIFY route_path_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE media MODIFY type_private TINYINT(1) DEFAULT 0 NULL');
        $this->addSql('ALTER TABLE media MODIFY sha1 VARCHAR(40) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->addSql('ALTER TABLE cms_block ALTER COLUMN id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_compiled_data ALTER COLUMN id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_compiled_data ALTER COLUMN content_version_id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_content ALTER COLUMN id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_content ALTER COLUMN published_version_id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_content ALTER COLUMN last_version_id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_content_page ALTER COLUMN id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_content_sites ALTER COLUMN content_id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_content_version ALTER COLUMN id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_content_version ALTER COLUMN content_id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_content_version_medias ALTER COLUMN content_version_id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_content_version_routes ALTER COLUMN content_version_id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_menu ALTER COLUMN menu_type TYPE CHAR(30)');
            $this->addSql('ALTER TABLE cms_menu ALTER COLUMN id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_menu_item ALTER COLUMN id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_menu_item ALTER COLUMN menu_id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_menu_item ALTER COLUMN parent_item_id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_route ALTER COLUMN content_id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_route_path ALTER COLUMN path TYPE CHAR(100)');
            $this->addSql('ALTER TABLE cms_route_path ALTER COLUMN compiled_path TYPE CHAR(255)');
            $this->addSql('ALTER TABLE cms_route_path ALTER COLUMN locale TYPE CHAR(5)');
            $this->addSql('ALTER TABLE cms_route_path ALTER COLUMN id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE cms_route_path_sites ALTER COLUMN route_path_id TYPE CHAR(36)');
            $this->addSql('ALTER TABLE media ALTER COLUMN type_private SET DEFAULT false');
            $this->addSql('ALTER TABLE media ALTER COLUMN type_private SET NOT NULL');
            $this->addSql('ALTER TABLE media ALTER COLUMN sha1 TYPE VARCHAR(60)');

            return;
        }

        $this->addSql('ALTER TABLE cms_block MODIFY id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_compiled_data MODIFY id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_compiled_data MODIFY content_version_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content MODIFY id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content MODIFY published_version_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content MODIFY last_version_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_page MODIFY id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_sites MODIFY content_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_version MODIFY id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_version MODIFY content_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_version_medias MODIFY content_version_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_version_routes MODIFY content_version_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_menu MODIFY menu_type CHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE cms_menu MODIFY id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_menu_item MODIFY id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_menu_item MODIFY menu_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_menu_item MODIFY parent_item_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route MODIFY content_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route_path MODIFY path CHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE cms_route_path MODIFY compiled_path CHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route_path MODIFY locale CHAR(5) NOT NULL');
        $this->addSql('ALTER TABLE cms_route_path MODIFY id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_route_path_sites MODIFY route_path_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE media MODIFY type_private TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE media MODIFY sha1 VARCHAR(60) DEFAULT NULL');
    }
}
