<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240724084510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Menus support data field for dynamic configuration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_menu ADD data JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_menu DROP data');
    }
}
