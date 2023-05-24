<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230522124841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add multisite feature';
    }

    public function up(Schema $schema): void
    {
        // create site table
        $this->addSql('CREATE TABLE cms_site (id CHAR(36) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT IGNORE INTO cms_site SELECT DISTINCT site FROM cms_content');
        $this->addSql('INSERT IGNORE INTO cms_site SELECT DISTINCT site FROM cms_route');

        // UPDATE CONTENT MODEL
        $this->addSql('CREATE TABLE cms_content_sites (content_id CHAR(36) NOT NULL, site_id CHAR(36) NOT NULL, INDEX IDX_E792456484A0A3ED (content_id), INDEX IDX_E7924564F6BD1646 (site_id), PRIMARY KEY(content_id, site_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;');
        $this->addSql('ALTER TABLE cms_content_sites ADD CONSTRAINT FK_E792456484A0A3ED FOREIGN KEY (content_id) REFERENCES cms_content (id) ON DELETE CASCADE;');
        $this->addSql('ALTER TABLE cms_content_sites ADD CONSTRAINT FK_E7924564F6BD1646 FOREIGN KEY (site_id) REFERENCES cms_site (id) ON DELETE RESTRICT;');
        $this->addSql('INSERT IGNORE INTO cms_content_sites SELECT id,site FROM cms_content;');
        $this->addSql('ALTER TABLE cms_content DROP site;');

        // UPDATE ROUTES MODEL
        $this->addSql('CREATE TABLE cms_route_sites (route_id VARCHAR(255) NOT NULL, site_id CHAR(36) NOT NULL, INDEX IDX_BC9E2F1934ECB4E6 (route_id), INDEX IDX_BC9E2F19F6BD1646 (site_id), PRIMARY KEY(route_id, site_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;');
        $this->addSql('CREATE TABLE cms_route_path_sites (route_path_id CHAR(36) NOT NULL, site_id CHAR(36) NOT NULL, INDEX IDX_D70A2920213F0BF3 (route_path_id), INDEX IDX_D70A2920F6BD1646 (site_id), PRIMARY KEY(route_path_id, site_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;');
        $this->addSql('ALTER TABLE cms_route_sites ADD CONSTRAINT FK_BC9E2F1934ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE CASCADE;');
        $this->addSql('ALTER TABLE cms_route_sites ADD CONSTRAINT FK_BC9E2F19F6BD1646 FOREIGN KEY (site_id) REFERENCES cms_site (id) ON DELETE RESTRICT;');
        $this->addSql('ALTER TABLE cms_route_path_sites ADD CONSTRAINT FK_D70A2920213F0BF3 FOREIGN KEY (route_path_id) REFERENCES cms_route_path (id) ON DELETE CASCADE;');
        $this->addSql('ALTER TABLE cms_route_path_sites ADD CONSTRAINT FK_D70A2920F6BD1646 FOREIGN KEY (site_id) REFERENCES cms_site (id) ON DELETE RESTRICT;');
        $this->addSql('INSERT IGNORE INTO cms_route_sites SELECT id,site FROM cms_route;');
        $this->addSql('ALTER TABLE cms_route DROP site;');
        $this->addSql('INSERT IGNORE INTO cms_route_path_sites SELECT id,site FROM cms_route_path;');
        $this->addSql('ALTER TABLE cms_route_path DROP site;');
    }

    public function down(Schema $schema): void
    {
        // UPDATE CONTENT MODEL
        $this->addSql('ALTER TABLE cms_content ADD site VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('UPDATE cms_content c SET site=(SELECT site FROM cms_content_sites cs WHERE cs.content_id=c.site LIMIT 1)');
        $this->addSql('DROP TABLE cms_content_sites');

        // UPDATE ROUTES MODEL
        $this->addSql('ALTER TABLE cms_route ADD site CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE cms_route_path ADD site CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');

        $this->addSql('UPDATE cms_route r SET site=(SELECT site_id FROM cms_route_sites rs WHERE rs.route_id=r.id LIMIT 1)');
        $this->addSql('DROP TABLE cms_route_sites');

        $this->addSql('UPDATE cms_route_path rp SET site=(SELECT site_id FROM cms_route_path_sites rps WHERE rps.route_path_id=rp.id LIMIT 1)');
        $this->addSql('DROP TABLE cms_route_path_sites');

        // drop site table
        $this->addSql('DROP TABLE cms_site');
    }
}
