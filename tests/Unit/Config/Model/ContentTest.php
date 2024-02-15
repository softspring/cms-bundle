<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Model;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\Model\Content;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Form\Admin\Content\ContentCreateForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentDeleteForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentImportForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentListFilterForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentRoutesForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentSeoForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentUpdateForm;
use Softspring\CmsBundle\Form\Admin\ContentVersion\VersionCreateForm;
use Softspring\CmsBundle\Form\Admin\ContentVersion\VersionImportForm;
use Softspring\CmsBundle\Form\Admin\ContentVersion\VersionListFilterForm;
use Softspring\CmsBundle\Form\Admin\ContentVersion\VersionUpdateForm;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ContentTest extends TestCase
{
    public function testEmptyRevision(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "revision" under "content" must be configured.');

        $processor = new Processor();
        $configuration = new Content('content_name');
        $processor->processConfiguration($configuration, []);
    }

    public function testEmptyEntityClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child config "entity_class" under "content" must be configured.');

        $processor = new Processor();
        $configuration = new Content('content_name');
        $processor->processConfiguration($configuration, [
            'block' => [
                'revision' => 1,
            ],
        ]);
    }

    public function testDefaultConfig()
    {
        $processor = new Processor();
        $configuration = new Content('content_name');
        $config = $processor->processConfiguration($configuration, [
            'block' => [
                'revision' => 1,
                'entity_class' => Page::class,
            ],
        ]);

        $this->assertEquals([
            'revision' => 1,
            'entity_class' => Page::class,
            'save_compiled' => true,
            'default_layout' => 'default',
            'containers' => [],
            'extra_fields' => [],
            'meta' => [],
            'seo' => [
                'metaTitle' => ['type' => 'translatable'],
                'metaDescription' => ['type' => 'translatable'],
                'metaKeywords' => ['type' => 'translatable'],
                'noIndex' => ['type' => 'checkbox', 'type_options' => ['required' => false]],
                'noFollow' => ['type' => 'checkbox', 'type_options' => ['required' => false]],
                'sitemap' => ['type' => 'checkbox', 'type_options' => ['required' => false]],
                'sitemapChangefreq' => ['type' => 'choice', 'type_options' => [
                    'choices' => [
                        'admin_page.form.seo.sitemapChangefreq.values.empty' => false,
                        'admin_page.form.seo.sitemapChangefreq.values.always' => 'always',
                        'admin_page.form.seo.sitemapChangefreq.values.hourly' => 'hourly',
                        'admin_page.form.seo.sitemapChangefreq.values.daily' => 'daily',
                        'admin_page.form.seo.sitemapChangefreq.values.weekly' => 'weekly',
                        'admin_page.form.seo.sitemapChangefreq.values.monthly' => 'monthly',
                        'admin_page.form.seo.sitemapChangefreq.values.yearly' => 'yearly',
                        'admin_page.form.seo.sitemapChangefreq.values.never' => 'never',
                    ],
                ]],
                'sitemapPriority' => ['type' => 'number', 'type_options' => ['required' => false, 'scale' => 1, 'constraints' => [[
                    'constraint' => 'range',
                    'options' => ['min' => 0, 'max' => 1],
                ]]]],
            ],
            'admin' => [
                'list' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_LIST',
                    'view' => '@SfsCms/admin/content/list.html.twig',
                    'page_view' => '@SfsCms/admin/content/list-page.html.twig',
                    'filter_form' => ContentListFilterForm::class,
                ],
                'create' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_CREATE',
                    'view' => '@SfsCms/admin/content/create.html.twig',
                    'type' => ContentCreateForm::class,
                    'success_redirect_to' => '',
                ],
                'import' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_IMPORT',
                    'view' => '@SfsCms/admin/content/import.html.twig',
                    'type' => ContentImportForm::class,
                    'success_redirect_to' => '',
                ],
                'version_import' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_IMPORT_VERSION',
                    'view' => '@SfsCms/admin/content/version_import.html.twig',
                    'type' => VersionImportForm::class,
                    'success_redirect_to' => '',
                ],
                'read' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_READ',
                    'view' => '@SfsCms/admin/content/read.html.twig',
                ],
                'preview' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_PREVIEW',
                    'view' => '@SfsCms/admin/content/preview.html.twig',
                ],
                'version_list' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_VERSIONS',
                    'view' => '@SfsCms/admin/content/version_list.html.twig',
                    'filter_form' => VersionListFilterForm::class,
                ],
                'version_preview' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_PREVIEW',
                ],
                'version_cleanup' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_CLEANUP_VERSIONS',
                ],
                'version_lock' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_KEEP_VERSION',
                ],
                'export_version' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_EXPORT_VERSION',
                ],
                'update' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_UPDATE',
                    'view' => '@SfsCms/admin/content/update.html.twig',
                    'type' => ContentUpdateForm::class,
                    'success_redirect_to' => '',
                ],
                'routes' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_ROUTES',
                    'view' => '@SfsCms/admin/content/routes.html.twig',
                    'type' => ContentRoutesForm::class,
                    'success_redirect_to' => '',
                ],
                'delete' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_DELETE',
                    'view' => '@SfsCms/admin/content/delete.html.twig',
                    'type' => ContentDeleteForm::class,
                    'success_redirect_to' => '',
                ],
                'seo' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_SEO',
                    'view' => '@SfsCms/admin/content/seo.html.twig',
                    'type' => ContentSeoForm::class,
                    'success_redirect_to' => '',
                ],
                'version_create' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_CONTENT',
                    'view' => '@SfsCms/admin/content/version_create.html.twig',
                    'type' => VersionCreateForm::class,
                    'success_redirect_to' => '',
                ],
                'publish_version' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_PUBLISH_VERSION',
                ],
                'unpublish' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_UNPUBLISH',
                ],
                'version_info' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_VERSION_INFO',
                    'view' => '@SfsCms/admin/content/version_info.html.twig',
                    'type' => VersionUpdateForm::class,
                    'success_redirect_to' => '',
                ],
                'version_recompile' => [
                    'is_granted' => 'PERMISSION_SFS_CMS_ADMIN_CONTENT_RECOMPILE_VERSION',
                ],
            ],
            'allowed_layouts' => [],
        ], $config);
    }
}
