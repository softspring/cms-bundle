<?php

namespace Softspring\CmsBundle\Config\Model;

use Softspring\CmsBundle\Form\Admin\Content\ContentContentForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentCreateForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentDeleteForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentImportForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentListFilterForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentSeoForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentUpdateForm;
use Softspring\CmsBundle\Form\Admin\Content\ContentVersionImportForm;
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
                        'metaTitle' => ['type' => 'translatableText'],
                        'metaDescription' => ['type' => 'translatableText'],
                        'metaKeywords' => ['type' => 'translatableText'],
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
                    ->children()
                        // TODO add events config
                        ->scalarNode('list_is_granted')->defaultValue('')->end()
                        ->scalarNode('list_view')->defaultValue('@SfsCms/admin/content/list.html.twig')->end()
                        ->scalarNode('list_page_view')->defaultValue('@SfsCms/admin/content/list-page.html.twig')->end()
                        ->scalarNode('list_filter_form')->defaultValue(ContentListFilterForm::class)->end()
                        ->scalarNode('create_is_granted')->defaultValue('')->end()
                        ->scalarNode('create_view')->defaultValue('@SfsCms/admin/content/create.html.twig')->end()
                        ->scalarNode('create_type')->defaultValue(ContentCreateForm::class)->end()
                        ->scalarNode('create_success_redirect_to')->defaultValue('')->end()
                        ->scalarNode('import_is_granted')->defaultValue('')->end()
                        ->scalarNode('import_view')->defaultValue('@SfsCms/admin/content/import.html.twig')->end()
                        ->scalarNode('import_type')->defaultValue(ContentImportForm::class)->end()
                        ->scalarNode('import_success_redirect_to')->defaultValue('')->end()
                        ->scalarNode('import_version_is_granted')->defaultValue('')->end()
                        ->scalarNode('import_version_view')->defaultValue('@SfsCms/admin/content/import_version.html.twig')->end()
                        ->scalarNode('import_version_type')->defaultValue(ContentVersionImportForm::class)->end()
                        ->scalarNode('import_version_success_redirect_to')->defaultValue('')->end()
                        ->scalarNode('read_view')->defaultValue('@SfsCms/admin/content/read.html.twig')->end()
                        ->scalarNode('preview_view')->defaultValue('@SfsCms/admin/content/preview.html.twig')->end()
                        ->scalarNode('versions_view')->defaultValue('@SfsCms/admin/content/versions.html.twig')->end()
                        ->scalarNode('update_is_granted')->defaultValue('')->end()
                        ->scalarNode('update_view')->defaultValue('@SfsCms/admin/content/update.html.twig')->end()
                        ->scalarNode('update_type')->defaultValue(ContentUpdateForm::class)->end()
                        ->scalarNode('update_success_redirect_to')->defaultValue('')->end()
                        ->scalarNode('delete_is_granted')->defaultValue('')->end()
                        ->scalarNode('delete_view')->defaultValue('@SfsCms/admin/content/delete.html.twig')->end()
                        ->scalarNode('delete_type')->defaultValue(ContentDeleteForm::class)->end()
                        ->scalarNode('delete_success_redirect_to')->defaultValue('')->end()
                        ->scalarNode('seo_is_granted')->defaultValue('')->end()
                        ->scalarNode('seo_view')->defaultValue('@SfsCms/admin/content/seo.html.twig')->end()
                        ->scalarNode('seo_type')->defaultValue(ContentSeoForm::class)->end()
                        ->scalarNode('seo_success_redirect_to')->defaultValue('')->end()
                        ->scalarNode('content_is_granted')->defaultValue('')->end()
                        ->scalarNode('content_view')->defaultValue('@SfsCms/admin/content/content.html.twig')->end()
                        ->scalarNode('content_type')->defaultValue(ContentContentForm::class)->end()
                        ->scalarNode('content_success_redirect_to')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
