<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240926071945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add compile errors flags';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_compiled_data ADD errors TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE cms_content_version ADD compile_errors TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_compiled_data DROP errors');
        $this->addSql('ALTER TABLE cms_content_version DROP compile_errors');
    }
}
