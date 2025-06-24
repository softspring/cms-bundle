<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250624105445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store section references in content and section versions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cms_content_version_sections (content_version_id CHAR(36) NOT NULL, section_id CHAR(36) NOT NULL, INDEX IDX_4491D9C7D28591F7 (content_version_id), INDEX IDX_4491D9C7D823E37A (section_id), PRIMARY KEY(content_version_id, section_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_section_version_sections (section_version_id CHAR(36) NOT NULL, section_id CHAR(36) NOT NULL, INDEX IDX_1CEDC3D1D60C1DDA (section_version_id), INDEX IDX_1CEDC3D1D823E37A (section_id), PRIMARY KEY(section_version_id, section_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cms_content_version_sections ADD CONSTRAINT FK_4491D9C7D28591F7 FOREIGN KEY (content_version_id) REFERENCES cms_content_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_content_version_sections ADD CONSTRAINT FK_4491D9C7D823E37A FOREIGN KEY (section_id) REFERENCES cms_section (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE cms_section_version_sections ADD CONSTRAINT FK_1CEDC3D1D60C1DDA FOREIGN KEY (section_version_id) REFERENCES cms_section_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_section_version_sections ADD CONSTRAINT FK_1CEDC3D1D823E37A FOREIGN KEY (section_id) REFERENCES cms_section (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_content_version_sections DROP FOREIGN KEY FK_4491D9C7D28591F7');
        $this->addSql('ALTER TABLE cms_content_version_sections DROP FOREIGN KEY FK_4491D9C7D823E37A');
        $this->addSql('ALTER TABLE cms_section_version_sections DROP FOREIGN KEY FK_1CEDC3D1D60C1DDA');
        $this->addSql('ALTER TABLE cms_section_version_sections DROP FOREIGN KEY FK_1CEDC3D1D823E37A');
        $this->addSql('DROP TABLE cms_content_version_sections');
        $this->addSql('DROP TABLE cms_section_version_sections');
    }
}
