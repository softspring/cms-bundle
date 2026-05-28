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

        $this->addSql('ALTER TABLE cms_compiled_data DROP FOREIGN KEY FK_30483E79D28591F7');
        $this->addSql('ALTER TABLE cms_content DROP FOREIGN KEY FK_A0293FB8A2C84DEF');
        $this->addSql('ALTER TABLE cms_content DROP FOREIGN KEY FK_A0293FB8B5D68A8D');
        $this->addSql('ALTER TABLE cms_content_blog_article DROP FOREIGN KEY FK_7BB3D8EFBF396750');
        $this->addSql('ALTER TABLE cms_content_page DROP FOREIGN KEY FK_584BAC9FBF396750');
        $this->addSql('ALTER TABLE cms_content_project DROP FOREIGN KEY FK_5047E252BF396750');
        $this->addSql('ALTER TABLE cms_content_sites DROP FOREIGN KEY FK_E792456484A0A3ED');
        $this->addSql('ALTER TABLE cms_content_technology DROP FOREIGN KEY FK_6F873732BF396750');
        $this->addSql('ALTER TABLE cms_content_version DROP FOREIGN KEY FK_C0E8E17F84A0A3ED');
        $this->addSql('ALTER TABLE cms_content_version_medias DROP FOREIGN KEY FK_423042FFD28591F7');
        $this->addSql('ALTER TABLE cms_content_version_routes DROP FOREIGN KEY FK_62372FCDD28591F7');
        $this->addSql('ALTER TABLE cms_content_version_sections DROP FOREIGN KEY FK_4491D9C7D28591F7');
        $this->addSql('ALTER TABLE cms_menu_item DROP FOREIGN KEY FK_1432B53D60272618');
        $this->addSql('ALTER TABLE cms_menu_item DROP FOREIGN KEY FK_1432B53DCCD7E912');
        $this->addSql('ALTER TABLE cms_route DROP FOREIGN KEY FK_2CB7BB5584A0A3ED');
        $this->addSql('ALTER TABLE cms_route_path_sites DROP FOREIGN KEY FK_D70A2920213F0BF3');
        $this->addSql('ALTER TABLE cms_block CHANGE id id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_compiled_data CHANGE id id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_compiled_data CHANGE content_version_id content_version_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content CHANGE id id VARCHAR(36) NOT NULL, CHANGE published_version_id published_version_id VARCHAR(36) DEFAULT NULL, CHANGE last_version_id last_version_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_sites DROP FOREIGN KEY `FK_E7924564F6BD1646`');
        $this->addSql('ALTER TABLE cms_content_sites CHANGE content_id content_id VARCHAR(36) NOT NULL, CHANGE site_id site_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_page CHANGE id id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_project CHANGE id id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_technology CHANGE id id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_version CHANGE id id VARCHAR(36) NOT NULL, CHANGE content_id content_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_version_medias CHANGE content_version_id content_version_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_version_routes CHANGE content_version_id content_version_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_version_sections CHANGE content_version_id content_version_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_menu CHANGE id id VARCHAR(36) NOT NULL, CHANGE menu_type menu_type VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE cms_menu_item CHANGE id id VARCHAR(36) NOT NULL, CHANGE menu_id menu_id VARCHAR(36) DEFAULT NULL, CHANGE parent_item_id parent_item_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route CHANGE content_id content_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route_sites DROP FOREIGN KEY `FK_BC9E2F19F6BD1646`');
        $this->addSql('ALTER TABLE cms_route_sites CHANGE site_id site_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_route_path CHANGE id id VARCHAR(36) NOT NULL, CHANGE path path VARCHAR(100) NOT NULL, CHANGE locale locale VARCHAR(5) DEFAULT NULL, CHANGE compiled_path compiled_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route_path_sites DROP FOREIGN KEY `FK_D70A2920F6BD1646`');
        $this->addSql('ALTER TABLE cms_route_path_sites CHANGE route_path_id route_path_id VARCHAR(36) NOT NULL, CHANGE site_id site_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_site CHANGE id id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_compiled_data ADD CONSTRAINT FK_30483E79D28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content ADD CONSTRAINT FK_A0293FB8A2C84DEF FOREIGN KEY (last_version_id) REFERENCES cms_content_version (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cms_content ADD CONSTRAINT FK_A0293FB8B5D68A8D FOREIGN KEY (published_version_id) REFERENCES cms_content_version (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cms_content_blog_article ADD CONSTRAINT FK_7BB3D8EFBF396750 FOREIGN KEY (id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_page ADD CONSTRAINT FK_584BAC9FBF396750 FOREIGN KEY (id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_project ADD CONSTRAINT FK_5047E252BF396750 FOREIGN KEY (id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_sites ADD CONSTRAINT FK_E792456484A0A3ED FOREIGN KEY (content_id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_sites ADD CONSTRAINT FK_E7924564F6BD1646 FOREIGN KEY (site_id) REFERENCES cms_site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_technology ADD CONSTRAINT FK_6F873732BF396750 FOREIGN KEY (id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version ADD CONSTRAINT FK_C0E8E17F84A0A3ED FOREIGN KEY (content_id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version_medias ADD CONSTRAINT FK_423042FFD28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version_routes ADD CONSTRAINT FK_62372FCDD28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version_sections ADD CONSTRAINT FK_4491D9C7D28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_menu_item ADD CONSTRAINT FK_1432B53D60272618 FOREIGN KEY (parent_item_id) REFERENCES cms_menu_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_menu_item ADD CONSTRAINT FK_1432B53DCCD7E912 FOREIGN KEY (menu_id) REFERENCES cms_menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route ADD CONSTRAINT FK_2CB7BB5584A0A3ED FOREIGN KEY (content_id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route_path_sites ADD CONSTRAINT FK_D70A2920213F0BF3 FOREIGN KEY (route_path_id) REFERENCES cms_route_path (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route_path_sites ADD CONSTRAINT FK_D70A2920F6BD1646 FOREIGN KEY (site_id) REFERENCES cms_site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route_sites ADD CONSTRAINT FK_BC9E2F19F6BD1646 FOREIGN KEY (site_id) REFERENCES cms_site (id) ON DELETE CASCADE');
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

        $this->addSql('ALTER TABLE cms_compiled_data DROP FOREIGN KEY FK_30483E79D28591F7');
        $this->addSql('ALTER TABLE cms_content DROP FOREIGN KEY FK_A0293FB8A2C84DEF');
        $this->addSql('ALTER TABLE cms_content DROP FOREIGN KEY FK_A0293FB8B5D68A8D');
        $this->addSql('ALTER TABLE cms_content_blog_article DROP FOREIGN KEY FK_7BB3D8EFBF396750');
        $this->addSql('ALTER TABLE cms_content_page DROP FOREIGN KEY FK_584BAC9FBF396750');
        $this->addSql('ALTER TABLE cms_content_project DROP FOREIGN KEY FK_5047E252BF396750');
        $this->addSql('ALTER TABLE cms_content_sites DROP FOREIGN KEY FK_E792456484A0A3ED');
        $this->addSql('ALTER TABLE cms_content_technology DROP FOREIGN KEY FK_6F873732BF396750');
        $this->addSql('ALTER TABLE cms_content_version DROP FOREIGN KEY FK_C0E8E17F84A0A3ED');
        $this->addSql('ALTER TABLE cms_content_version_medias DROP FOREIGN KEY FK_423042FFD28591F7');
        $this->addSql('ALTER TABLE cms_content_version_routes DROP FOREIGN KEY FK_62372FCDD28591F7');
        $this->addSql('ALTER TABLE cms_content_version_sections DROP FOREIGN KEY FK_4491D9C7D28591F7');
        $this->addSql('ALTER TABLE cms_menu_item DROP FOREIGN KEY FK_1432B53D60272618');
        $this->addSql('ALTER TABLE cms_menu_item DROP FOREIGN KEY FK_1432B53DCCD7E912');
        $this->addSql('ALTER TABLE cms_route DROP FOREIGN KEY FK_2CB7BB5584A0A3ED');
        $this->addSql('ALTER TABLE cms_route_path_sites DROP FOREIGN KEY FK_D70A2920213F0BF3');
        $this->addSql('ALTER TABLE cms_block CHANGE id id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_compiled_data CHANGE id id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_compiled_data CHANGE content_version_id content_version_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content CHANGE id id CHAR(36) NOT NULL, CHANGE published_version_id published_version_id CHAR(36) DEFAULT NULL, CHANGE last_version_id last_version_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_page CHANGE id id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_project CHANGE id id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_sites DROP FOREIGN KEY FK_E7924564F6BD1646');
        $this->addSql('ALTER TABLE cms_content_sites CHANGE content_id content_id CHAR(36) NOT NULL, CHANGE site_id site_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_technology CHANGE id id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_version CHANGE id id CHAR(36) NOT NULL, CHANGE content_id content_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_version_medias CHANGE content_version_id content_version_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_version_routes CHANGE content_version_id content_version_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_content_version_sections CHANGE content_version_id content_version_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_menu CHANGE menu_type menu_type CHAR(30) NOT NULL, CHANGE id id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_menu_item CHANGE id id CHAR(36) NOT NULL, CHANGE menu_id menu_id CHAR(36) DEFAULT NULL, CHANGE parent_item_id parent_item_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route CHANGE content_id content_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route_path CHANGE path path CHAR(100) NOT NULL, CHANGE compiled_path compiled_path CHAR(255) DEFAULT NULL, CHANGE locale locale CHAR(5) DEFAULT NULL, CHANGE id id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_route_path_sites DROP FOREIGN KEY FK_D70A2920F6BD1646');
        $this->addSql('ALTER TABLE cms_route_path_sites CHANGE route_path_id route_path_id CHAR(36) NOT NULL, CHANGE site_id site_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_route_sites DROP FOREIGN KEY FK_BC9E2F19F6BD1646');
        $this->addSql('ALTER TABLE cms_route_sites CHANGE site_id site_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_site CHANGE id id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE cms_compiled_data ADD CONSTRAINT FK_30483E79D28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content ADD CONSTRAINT FK_A0293FB8A2C84DEF FOREIGN KEY (last_version_id) REFERENCES cms_content_version (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cms_content ADD CONSTRAINT FK_A0293FB8B5D68A8D FOREIGN KEY (published_version_id) REFERENCES cms_content_version (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cms_content_blog_article ADD CONSTRAINT FK_7BB3D8EFBF396750 FOREIGN KEY (id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_page ADD CONSTRAINT FK_584BAC9FBF396750 FOREIGN KEY (id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_project ADD CONSTRAINT FK_5047E252BF396750 FOREIGN KEY (id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_sites ADD CONSTRAINT FK_E792456484A0A3ED FOREIGN KEY (content_id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_sites ADD CONSTRAINT FK_E7924564F6BD1646 FOREIGN KEY (site_id) REFERENCES cms_site (id) ON UPDATE NO ACTION');
        $this->addSql('ALTER TABLE cms_content_technology ADD CONSTRAINT FK_6F873732BF396750 FOREIGN KEY (id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version ADD CONSTRAINT FK_C0E8E17F84A0A3ED FOREIGN KEY (content_id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version_medias ADD CONSTRAINT FK_423042FFD28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version_routes ADD CONSTRAINT FK_62372FCDD28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version_sections ADD CONSTRAINT FK_4491D9C7D28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_menu_item ADD CONSTRAINT FK_1432B53D60272618 FOREIGN KEY (parent_item_id) REFERENCES cms_menu_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_menu_item ADD CONSTRAINT FK_1432B53DCCD7E912 FOREIGN KEY (menu_id) REFERENCES cms_menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route ADD CONSTRAINT FK_2CB7BB5584A0A3ED FOREIGN KEY (content_id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route_path_sites ADD CONSTRAINT FK_D70A2920213F0BF3 FOREIGN KEY (route_path_id) REFERENCES cms_route_path (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route_path_sites ADD CONSTRAINT FK_D70A2920F6BD1646 FOREIGN KEY (site_id) REFERENCES cms_site (id) ON UPDATE NO ACTION');
        $this->addSql('ALTER TABLE cms_route_sites ADD CONSTRAINT FK_BC9E2F19F6BD1646 FOREIGN KEY (site_id) REFERENCES cms_site (id) ON UPDATE NO ACTION');
    }
}
