<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250619144132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add section extra_data field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_section ADD extra_data JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_section DROP extra_data');
    }
}
