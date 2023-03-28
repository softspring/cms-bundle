<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230328091638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Support parent routes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_route ADD parent_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_route ADD CONSTRAINT FK_2CB7BB55727ACA70 FOREIGN KEY (parent_id) REFERENCES cms_route (id) ON DELETE RESTRICT');
        $this->addSql('CREATE INDEX IDX_2CB7BB55727ACA70 ON cms_route (parent_id)');
        $this->addSql('ALTER TABLE cms_route_path ADD compiled_path CHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE cms_route_path SET compiled_path = path');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_route DROP FOREIGN KEY FK_2CB7BB55727ACA70');
        $this->addSql('DROP INDEX IDX_2CB7BB55727ACA70 ON cms_route');
        $this->addSql('ALTER TABLE cms_route DROP parent_id');
        $this->addSql('ALTER TABLE cms_route_path DROP compiled_path');
    }
}
