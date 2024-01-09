<?php

namespace Softspring\CmsBundle\Config\Model;

use Softspring\CmsBundle\Form\Admin\Content\ContentCreateForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentDeleteForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentImportForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentListFilterForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentSeoForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentUpdateForm;
use Softspring\CmsBundle\Form\Admin\ContentVersion\VersionCreateForm;
use Softspring\CmsBundle\Form\Admin\ContentVersion\VersionImportForm;
use Softspring\CmsBundle\Form\Admin\ContentVersion\VersionListFilterForm;
use Softspring\CmsBundle\Form\Admin\ContentVersion\VersionUpdateForm;
use Softspring\CmsBundle\Form\Admin\ContentVersion\VersionTranslateForm;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Content implements ConfigurationInterface
{
    protected string $contentName;

    public function __construct(string $contentName)
    {
        $this->contentName = $contentName;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('content');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode('revision')->isRequired()->end()
                ->booleanNode('save_compiled')->defaultTrue()->end()

                ->scalarNode('entity_class')->isRequired()->end()
                ->scalarNode('default_layout')->defaultValue('default')->end()

                ->arrayNode('containers')
                    ->defaultValue([])
                    ->useAttributeAsKey('key')
                    ->arrayPrototype()
                        ->children()
                            ->booleanNode('required')->defaultFalse()->end()
                            ->arrayNode('allowed_modules')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('extra_fields')
                    ->defaultValue([])
                    ->useAttributeAsKey('key')
                    ->arrayPrototype()
                    ->children()
                        ->scalarNode('type')->isRequired()->end()
                        ->arrayNode('type_options')
                            ->useAttributeAsKey('key')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                    ->end()
                ->end()

                ->arrayNode('meta')
                    ->variablePrototype()
                    ->end()
                ->end()

                ->arrayNode('seo')
                    ->defaultValue([
                        'metaTitle' => ['type' => 'translatable'],
                        'metaDescription' => ['type' => 'translatable'],
                        'metaKeywords' => ['type' => 'translatable'],
                        'noIndex' => ['type' => 'checkbox', 'type_options' => ['required' => false]],
                        'noFollow' => ['type' => 'checkbox', 'type_options' => ['required' => false]],
                        'sitemap' => ['type' => 'checkbox', 'type_options' => ['required' => false]],
                        'sitemapChangefreq' => ['type' => 'choice', 'type_options' => [
                            'choices' => [
                                'admin_page.form.seo.sitemapChangefreq.values.empty' => '',
                                'admin_page.form.seo.sitemapChangefreq.values.always' => 'always',
                                'admin_page.form.seo.sitemapChangefreq.values.hourly' => 'hourly',
                                'admin_page.form.seo.sitemapChangefreq.values.daily' => 'daily',
                                'admin_page.form.seo.sitemapChangefreq.values.weekly' => 'weekly',
                                'admin_page.form.seo.sitemapChangefreq.values.monthly' => 'monthly',
                                'admin_page.form.seo.sitemapChangefreq.values.yearly' => 'yearly',
                                'admin_page.form.seo.sitemapChangefreq.values.never' => 'never',
                            ],
                        ]],
                        'sitemapPriority' => ['type' => 'number', 'type_options' => [
                            'required' => false,
                            'scale' => 1,
                            'constraints' => [
                                ['constraint' => 'range', 'options' => ['min' => 0, 'max' => 1]],
                            ],
                        ]],
                    ])
                    ->useAttributeAsKey('key')
                    ->arrayPrototype()
                    ->children()
                        ->scalarNode('type')->isRequired()->end()
                        ->arrayNode('type_options')
                            ->useAttributeAsKey('key')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                    ->end()
                ->end()

                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->beforeNormalization()
                        ->always()
                        ->then(function ($data): array {
                            $deprecatedOptions = [
                                'list_is_granted' => 'list.is_granted',
                                'list_view' => 'list.view',
                                'list_page_view' => 'list.page_view',
                                'list_filter_form' => 'list.filter_form',
                                'create_is_granted' => 'create.is_granted',
                                'create_view' => 'create.view',
                                'create_type' => 'create.type',
                                'create_success_redirect_to' => 'create.success_redirect_to',
                                'import_is_granted' => 'import.is_granted',
                                'import_view' => 'import.view',
                                'import_type' => 'import.type',
                                'import_success_redirect_to' => 'import.success_redirect_to',
                                'version_import_is_granted' => 'import.version_is_granted',
                                'version_import_view' => 'import.version_view',
                                'version_import_type' => 'import.version_type',
                                'version_import_success_redirect_to' => 'import.version_success_redirect_to',
                                'read_is_granted' => 'read.is_granted',
                                'read_view' => 'read.view',
                                'preview_is_granted' => 'preview.is_granted',
                                'preview_view' => 'preview.view',
                                'versions_is_granted' => 'version_list.is_granted',
                                'versions_view' => 'version_list.view',
                                'cleanup_versions_is_granted' => 'version_cleanup.is_granted',
                                'keep_version_is_granted' => 'version_lock.is_granted',
                                'export_version_is_granted' => 'export_version.is_granted',
                                'update_is_granted' => 'update.is_granted',
                                'update_view' => 'update.view',
                                'update_type' => 'update.type',
                                'update_success_redirect_to' => 'update.success_redirect_to',
                                'delete_is_granted' => 'delete.is_granted',
                                'delete_view' => 'delete.view',
                                'delete_type' => 'delete.type',
                                'delete_success_redirect_to' => 'delete.success_redirect_to',
                                'seo_is_granted' => 'seo.is_granted',
                                'seo_view' => 'seo.view',
                                'seo_type' => 'seo.type',
                                'seo_success_redirect_to' => 'seo.success_redirect_to',
                                'content_is_granted' => 'version_create.is_granted',
                                'content_view' => 'version_create.view',
                                'content_type' => 'version_create.type',
                                'content_success_redirect_to' => 'version_create.success_redirect_to',
                                'publish_version_is_granted' => 'publish_version.is_granted',
                                'unpublish_is_granted' => 'unpublish.is_granted',
                            ];

                            foreach ($deprecatedOptions as $deprecatedOption => $newOption) {
                                if (isset($data[$deprecatedOption])) {
                                    trigger_deprecation('softspring/cms-bundle', '5.2', 'The "%s" option is deprecated, use "%s" instead.', $deprecatedOption, $newOption);
                                    [$group, $attribute] = explode('.', $newOption);
                                    $data[$group][$attribute] = $data[$group][$attribute] ?? $data[$deprecatedOption];
                                    unset($data[$deprecatedOption]);
                                }
                            }

                            return $data;
                        })
                    ->end()
                    ->children()

                        ->scalarNode('list_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('list_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('list_page_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('list_filter_form')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('create_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('create_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('create_type')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('create_success_redirect_to')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('import_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('import_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('import_type')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('import_success_redirect_to')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('version_import_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('version_import_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('version_import_type')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('version_import_success_redirect_to')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('read_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('read_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('preview_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('preview_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('versions_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('versions_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('cleanup_versions_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('keep_version_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('export_version_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('update_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('update_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('update_type')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('update_success_redirect_to')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('delete_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('delete_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('delete_type')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('delete_success_redirect_to')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('seo_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('seo_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('seo_type')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('seo_success_redirect_to')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('content_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('content_view')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('content_type')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('content_success_redirect_to')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('publish_version_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()
                        ->scalarNode('unpublish_is_granted')->setDeprecated('softspring/cms-bundle', '5.2')->end()

                        ->arrayNode('list')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_LIST')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/list.html.twig')->end()
                                ->scalarNode('page_view')->defaultValue('@SfsCms/admin/content/list-page.html.twig')->end()
                                ->scalarNode('filter_form')->defaultValue(ContentListFilterForm::class)->end()
                            ->end()
                        ->end()

                        ->arrayNode('create')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_CREATE')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/create.html.twig')->end()
                                ->scalarNode('type')->defaultValue(ContentCreateForm::class)->end()
                                ->scalarNode('success_redirect_to')->defaultValue('')->end()
                            ->end()
                        ->end()

                        ->arrayNode('import')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_IMPORT')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/import.html.twig')->end()
                                ->scalarNode('type')->defaultValue(ContentImportForm::class)->end()
                                ->scalarNode('success_redirect_to')->defaultValue('')->end()
                            ->end()
                        ->end()

                        ->arrayNode('version_import')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_IMPORT_VERSION')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/version_import.html.twig')->end()
                                ->scalarNode('type')->defaultValue(VersionImportForm::class)->end()
                                ->scalarNode('success_redirect_to')->defaultValue('')->end()
                            ->end()
                        ->end()

                        ->arrayNode('read')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_READ')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/read.html.twig')->end()
                            ->end()
                        ->end()

                        ->arrayNode('preview')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_PREVIEW')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/preview.html.twig')->end()
                            ->end()
                        ->end()

                        ->arrayNode('version_list')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_VERSIONS')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/version_list.html.twig')->end()
                                ->scalarNode('filter_form')->defaultValue(VersionListFilterForm::class)->end()
                            ->end()
                        ->end()

                        ->arrayNode('version_preview')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_PREVIEW')->end()
                            ->end()
                        ->end()

                        ->arrayNode('version_cleanup')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_CLEANUP_VERSIONS')->end()
                            ->end()
                        ->end()
                        ->arrayNode('version_lock')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_KEEP_VERSION')->end()
                            ->end()
                        ->end()
                        ->arrayNode('version_recompile')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_RECOMPILE_VERSION')->end()
                            ->end()
                        ->end()
                        ->arrayNode('export_version')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_EXPORT_VERSION')->end()
                            ->end()
                        ->end()

                        ->arrayNode('update')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_UPDATE')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/update.html.twig')->end()
                                ->scalarNode('type')->defaultValue(ContentUpdateForm::class)->end()
                                ->scalarNode('success_redirect_to')->defaultValue('')->end()
                            ->end()
                        ->end()

                        ->arrayNode('delete')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_DELETE')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/delete.html.twig')->end()
                                ->scalarNode('type')->defaultValue(ContentDeleteForm::class)->end()
                                ->scalarNode('success_redirect_to')->defaultValue('')->end()
                            ->end()
                        ->end()

                        ->arrayNode('seo')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_SEO')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/seo.html.twig')->end()
                                ->scalarNode('type')->defaultValue(ContentSeoForm::class)->end()
                                ->scalarNode('success_redirect_to')->defaultValue('')->end()
                            ->end()
                        ->end()

                        ->arrayNode('version_create')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_CONTENT')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/version_create.html.twig')->end()
                                ->scalarNode('type')->defaultValue(VersionCreateForm::class)->end()
                                ->scalarNode('success_redirect_to')->defaultValue('')->end()
                            ->end()
                        ->end()

                        ->arrayNode('version_info')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_VERSION_INFO')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/version_info.html.twig')->end()
                                ->scalarNode('type')->defaultValue(VersionUpdateForm::class)->end()
                                ->scalarNode('success_redirect_to')->defaultValue('')->end()
                            ->end()
                        ->end()

                        ->arrayNode('version_translations')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_TRANSLATIONS')->end()
                                ->scalarNode('view')->defaultValue('@SfsCms/admin/content/version_translations.html.twig')->end()
                                ->scalarNode('type')->defaultValue(VersionTranslateForm::class)->end()
                                ->scalarNode('success_redirect_to')->defaultValue('')->end()
                            ->end()
                        ->end()

                        ->arrayNode('publish_version')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_PUBLISH_VERSION')->end()
                            ->end()
                        ->end()
                        ->arrayNode('unpublish')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_UNPUBLISH')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
