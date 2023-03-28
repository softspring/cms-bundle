<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230301000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create CMS database structure for 5.1';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cms_block (id CHAR(36) NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(100) NOT NULL, data JSON DEFAULT NULL, UNIQUE INDEX UNIQ_AD680C0E5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_content (id CHAR(36) NOT NULL, published_version_id CHAR(36) DEFAULT NULL, name VARCHAR(255) NOT NULL, extra_data JSON DEFAULT NULL, seo JSON DEFAULT NULL, discr VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_A0293FB85E237E06 (name), INDEX IDX_A0293FB8B5D68A8D (published_version_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_content_page (id CHAR(36) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_content_version (id CHAR(36) NOT NULL, content_id CHAR(36) DEFAULT NULL, layout VARCHAR(255) DEFAULT NULL, data JSON DEFAULT NULL, compiled_modules JSON DEFAULT NULL, compiled JSON DEFAULT NULL, created_at INT UNSIGNED DEFAULT NULL, keep TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_C0E8E17F84A0A3ED (content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_menu (id CHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, menu_type CHAR(30) NOT NULL, UNIQUE INDEX UNIQ_BA9397EE5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_menu_item (id CHAR(36) NOT NULL, menu_id CHAR(36) DEFAULT NULL, parent_item_id CHAR(36) DEFAULT NULL, route_id VARCHAR(255) DEFAULT NULL, item_type SMALLINT NOT NULL, item_text JSON NOT NULL, options JSON DEFAULT NULL, INDEX IDX_1432B53DCCD7E912 (menu_id), INDEX IDX_1432B53D60272618 (parent_item_id), INDEX IDX_1432B53D34ECB4E6 (route_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_route (id VARCHAR(255) NOT NULL, content_id CHAR(36) DEFAULT NULL, route_type SMALLINT NOT NULL, redirect_type SMALLINT DEFAULT NULL, redirect_url LONGTEXT DEFAULT NULL, symfony_route VARCHAR(255) DEFAULT NULL, INDEX IDX_2CB7BB5584A0A3ED (content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_route_path (id CHAR(36) NOT NULL, route_id VARCHAR(255) DEFAULT NULL, path CHAR(100) NOT NULL, cache_ttl INT UNSIGNED DEFAULT NULL, locale CHAR(5) DEFAULT NULL, UNIQUE INDEX UNIQ_95D1F10FB548B0F (path), INDEX IDX_95D1F10F34ECB4E6 (route_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cms_content ADD CONSTRAINT FK_A0293FB8B5D68A8D FOREIGN KEY (published_version_id) REFERENCES cms_content_version (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cms_content_page ADD CONSTRAINT FK_584BAC9FBF396750 FOREIGN KEY (id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version ADD CONSTRAINT FK_C0E8E17F84A0A3ED FOREIGN KEY (content_id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_menu_item ADD CONSTRAINT FK_1432B53DCCD7E912 FOREIGN KEY (menu_id) REFERENCES cms_menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_menu_item ADD CONSTRAINT FK_1432B53D60272618 FOREIGN KEY (parent_item_id) REFERENCES cms_menu_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_menu_item ADD CONSTRAINT FK_1432B53D34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route ADD CONSTRAINT FK_2CB7BB5584A0A3ED FOREIGN KEY (content_id) REFERENCES cms_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_route_path ADD CONSTRAINT FK_95D1F10F34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content ADD site VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route ADD site VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_95D1F10FB548B0F ON cms_route_path');
        $this->addSql('ALTER TABLE cms_content_version ADD version_number INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content ADD version_number INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content CHANGE version_number last_version_number INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content_version ADD origin SMALLINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_menu_item DROP FOREIGN KEY FK_1432B53D34ECB4E6');
        $this->addSql('DROP INDEX IDX_1432B53D34ECB4E6 ON cms_menu_item');
        $this->addSql('ALTER TABLE cms_menu_item ADD symfony_route JSON DEFAULT NULL, DROP route_id');
        $this->addSql('ALTER TABLE cms_route CHANGE symfony_route symfony_route JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_block ADD publish_start_date INT UNSIGNED DEFAULT NULL, ADD publish_end_date INT UNSIGNED DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_block DROP publish_start_date, DROP publish_end_date');
        $this->addSql('ALTER TABLE cms_content_version DROP origin');
        $this->addSql('ALTER TABLE cms_menu_item ADD route_id VARCHAR(255) DEFAULT NULL, DROP symfony_route');
        $this->addSql('ALTER TABLE cms_menu_item ADD CONSTRAINT FK_1432B53D34ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_1432B53D34ECB4E6 ON cms_menu_item (route_id)');
        $this->addSql('ALTER TABLE cms_route CHANGE symfony_route symfony_route VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content CHANGE last_version_number version_number INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content DROP version_number');
        $this->addSql('ALTER TABLE cms_content_version DROP version_number');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_95D1F10FB548B0F ON cms_route_path (path)');
        $this->addSql('ALTER TABLE cms_content DROP site');
        $this->addSql('ALTER TABLE cms_route DROP site');
        $this->addSql('ALTER TABLE cms_content_page DROP FOREIGN KEY FK_584BAC9FBF396750');
        $this->addSql('ALTER TABLE cms_content_version DROP FOREIGN KEY FK_C0E8E17F84A0A3ED');
        $this->addSql('ALTER TABLE cms_route DROP FOREIGN KEY FK_2CB7BB5584A0A3ED');
        $this->addSql('ALTER TABLE cms_content DROP FOREIGN KEY FK_A0293FB8B5D68A8D');
        $this->addSql('ALTER TABLE cms_menu_item DROP FOREIGN KEY FK_1432B53DCCD7E912');
        $this->addSql('ALTER TABLE cms_menu_item DROP FOREIGN KEY FK_1432B53D60272618');
        $this->addSql('ALTER TABLE cms_menu_item DROP FOREIGN KEY FK_1432B53D34ECB4E6');
        $this->addSql('ALTER TABLE cms_route_path DROP FOREIGN KEY FK_95D1F10F34ECB4E6');
        $this->addSql('DROP TABLE cms_block');
        $this->addSql('DROP TABLE cms_content');
        $this->addSql('DROP TABLE cms_content_page');
        $this->addSql('DROP TABLE cms_content_version');
        $this->addSql('DROP TABLE cms_menu');
        $this->addSql('DROP TABLE cms_menu_item');
        $this->addSql('DROP TABLE cms_route');
        $this->addSql('DROP TABLE cms_route_path');
    }
}
