<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231229074457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store contents locales info';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_content ADD default_locale VARCHAR(255) NOT NULL, ADD locales JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_content DROP default_locale, DROP locales');
    }
}
