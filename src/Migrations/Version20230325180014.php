<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230325180014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_route_path ADD site VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE cms_route_path rp SET site = (SELECT site FROM cms_route r WHERE r.id = rp.route_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_route_path DROP site');
    }
}
