<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230525113210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store sites config';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_site ADD config JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_site DROP config');
    }
}
