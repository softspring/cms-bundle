<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250612105522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add section database structure';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cms_section (id CHAR(36) NOT NULL, published_version_id CHAR(36) DEFAULT NULL, last_version_id CHAR(36) DEFAULT NULL, name VARCHAR(255) NOT NULL, last_version_number INT UNSIGNED DEFAULT NULL, last_modified INT UNSIGNED DEFAULT NULL, UNIQUE INDEX UNIQ_739F75FE5E237E06 (name), INDEX IDX_739F75FEB5D68A8D (published_version_id), INDEX IDX_739F75FEA2C84DEF (last_version_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_section_version (id CHAR(36) NOT NULL, section_id CHAR(36) DEFAULT NULL, data JSON DEFAULT NULL, meta JSON DEFAULT NULL, origin SMALLINT UNSIGNED DEFAULT NULL, origin_description VARCHAR(255) DEFAULT NULL, note VARCHAR(255) DEFAULT NULL, created_at INT UNSIGNED DEFAULT NULL, version_number INT UNSIGNED DEFAULT NULL, keep TINYINT(1) DEFAULT 0 NOT NULL, compile_errors TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_CDA7F5C3D823E37A (section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_section_version_medias (section_version_id CHAR(36) NOT NULL, media_id CHAR(36) NOT NULL, INDEX IDX_156B30E3D60C1DDA (section_version_id), INDEX IDX_156B30E3EA9FDD75 (media_id), PRIMARY KEY(section_version_id, media_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_section_version_routes (section_version_id CHAR(36) NOT NULL, route_id VARCHAR(100) NOT NULL, INDEX IDX_356C5DD1D60C1DDA (section_version_id), INDEX IDX_356C5DD134ECB4E6 (route_id), PRIMARY KEY(section_version_id, route_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cms_section ADD CONSTRAINT FK_739F75FEB5D68A8D FOREIGN KEY (published_version_id) REFERENCES cms_section_version (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cms_section ADD CONSTRAINT FK_739F75FEA2C84DEF FOREIGN KEY (last_version_id) REFERENCES cms_section_version (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cms_section_version ADD CONSTRAINT FK_CDA7F5C3D823E37A FOREIGN KEY (section_id) REFERENCES cms_section (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_section_version_medias ADD CONSTRAINT FK_156B30E3D60C1DDA FOREIGN KEY (section_version_id) REFERENCES cms_section_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_section_version_medias ADD CONSTRAINT FK_156B30E3EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE cms_section_version_routes ADD CONSTRAINT FK_356C5DD1D60C1DDA FOREIGN KEY (section_version_id) REFERENCES cms_section_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_section_version_routes ADD CONSTRAINT FK_356C5DD134ECB4E6 FOREIGN KEY (route_id) REFERENCES cms_route (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE cms_compiled_data ADD section_version_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_compiled_data ADD CONSTRAINT FK_30483E79D60C1DDA FOREIGN KEY (section_version_id) REFERENCES cms_section_version (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_30483E79D60C1DDA ON cms_compiled_data (section_version_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_compiled_data DROP FOREIGN KEY FK_30483E79D60C1DDA');
        $this->addSql('ALTER TABLE cms_section DROP FOREIGN KEY FK_739F75FEB5D68A8D');
        $this->addSql('ALTER TABLE cms_section DROP FOREIGN KEY FK_739F75FEA2C84DEF');
        $this->addSql('ALTER TABLE cms_section_version DROP FOREIGN KEY FK_CDA7F5C3D823E37A');
        $this->addSql('ALTER TABLE cms_section_version_medias DROP FOREIGN KEY FK_156B30E3D60C1DDA');
        $this->addSql('ALTER TABLE cms_section_version_medias DROP FOREIGN KEY FK_156B30E3EA9FDD75');
        $this->addSql('ALTER TABLE cms_section_version_routes DROP FOREIGN KEY FK_356C5DD1D60C1DDA');
        $this->addSql('ALTER TABLE cms_section_version_routes DROP FOREIGN KEY FK_356C5DD134ECB4E6');
        $this->addSql('DROP TABLE cms_section');
        $this->addSql('DROP TABLE cms_section_version');
        $this->addSql('DROP TABLE cms_section_version_medias');
        $this->addSql('DROP TABLE cms_section_version_routes');
        $this->addSql('DROP INDEX IDX_30483E79D60C1DDA ON cms_compiled_data');
        $this->addSql('ALTER TABLE cms_compiled_data DROP section_version_id');
    }
}
