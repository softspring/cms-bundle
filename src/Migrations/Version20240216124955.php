<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240216124955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add seo to versions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_content_version ADD seo JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE cms_content CHANGE seo indexing JSON DEFAULT NULL');

        // move seo from content to versions
        $this->addSql("UPDATE cms_content_version v SET seo = (SELECT JSON_REMOVE(indexing, '$.noIndex', '$.sitemap', '$.noFollow', '$.sitemapPriority', '$.sitemapChangefreq') FROM cms_content c WHERE v.content_id = c.id)");

        // remove seo from content
        $this->addSql("UPDATE cms_content SET indexing = JSON_REMOVE(indexing, '$.metaTitle', '$.metaKeywords', '$.metaDescription');");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms_content_version DROP seo');
        $this->addSql('ALTER TABLE cms_content CHANGE indexing seo JSON DEFAULT NULL');
    }
}
