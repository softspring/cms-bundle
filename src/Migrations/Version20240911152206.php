<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240911152206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add link attrs to cms_route, to use it republish content or modify anything on it.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_route ADD link_attrs VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_route DROP link_attrs');
    }
}
