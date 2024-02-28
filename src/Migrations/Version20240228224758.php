<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240228224758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add content lastModified';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_content ADD last_modified INT UNSIGNED DEFAULT NULL');
        $this->addSql('UPDATE cms_content c SET last_modified = IF(c.published_version_id IS NULL, NULL, (SELECT created_at FROM cms_content_version cv WHERE c.published_version_id = cv.id));');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_content DROP last_modified');
    }
}
