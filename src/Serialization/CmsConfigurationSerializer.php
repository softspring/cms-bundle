<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Serialization;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\SiteInterface;

use function array_keys;
use function count;

class CmsConfigurationSerializer
{
    public function __construct(
        private readonly CmsConfig $cmsConfig,
        private readonly SiteSerializer $siteSerializer,
        private readonly SensitiveValueSanitizer $sanitizer,
    ) {
    }

    public function counts(): array
    {
        return [
            'sites' => count($this->cmsConfig->getSites()),
            'layouts' => count($this->cmsConfig->getLayouts()),
            'modules' => count($this->cmsConfig->getModules(false)),
            'enabledModules' => count($this->cmsConfig->getModules(true)),
            'contents' => count($this->cmsConfig->getContents()),
            'menus' => count($this->cmsConfig->getMenus()),
            'blocks' => count($this->cmsConfig->getBlocks()),
            'plugins' => count($this->cmsConfig->getRegisteredPlugins()),
        ];
    }

    public function sites(bool $includeRawConfig): array
    {
        $sites = [];

        foreach ($this->cmsConfig->getSites() as $site) {
            if (!$site instanceof SiteInterface) {
                continue;
            }

            $sites[$site->getId()] = $this->siteSerializer->summarize($site, $includeRawConfig);
        }

        return $sites;
    }

    public function layouts(bool $includeRawConfig): array
    {
        $layouts = [];

        foreach ($this->cmsConfig->getLayouts() as $id => $config) {
            $layouts[$id] = [
                'id' => $id,
                'revision' => $config['revision'] ?? null,
                'enabled' => (bool) ($config['enabled'] ?? true),
                'saveCompiled' => (bool) ($config['save_compiled'] ?? true),
                'renderTemplate' => $config['render_template'] ?? null,
                'editTemplate' => $config['edit_template'] ?? null,
                'compatibleContents' => $config['compatible_contents'] ?? [],
                'containers' => $this->summarizeContainers($config['containers'] ?? []),
            ];

            if ($includeRawConfig) {
                $layouts[$id]['config'] = $this->sanitizer->sanitize($config);
            }
        }

        return $layouts;
    }

    public function modules(bool $includeRawConfig, bool $includeDisabledModules): array
    {
        $modules = [];

        foreach ($this->cmsConfig->getModules(false) as $id => $config) {
            $enabled = (bool) ($config['enabled'] ?? true);

            if (!$enabled && !$includeDisabledModules) {
                continue;
            }

            $modules[$id] = [
                'id' => $id,
                'revision' => $config['revision'] ?? null,
                'enabled' => $enabled,
                'group' => $config['group'] ?? 'default',
                'moduleType' => $config['module_type'] ?? null,
                'renderTemplate' => $config['render_template'] ?? null,
                'editTemplate' => $config['edit_template'] ?? null,
                'formTemplate' => $config['form_template'] ?? null,
                'compatibleContents' => $config['compatible_contents'] ?? [],
                'optionKeys' => array_keys($config['module_options'] ?? []),
                'revisionMigrationScripts' => $config['revision_migration_scripts'] ?? [],
            ];

            if ($includeRawConfig) {
                $modules[$id]['config'] = $this->sanitizer->sanitize($config);
            }
        }

        return $modules;
    }

    public function contents(bool $includeRawConfig): array
    {
        $contents = [];

        foreach ($this->cmsConfig->getContents() as $id => $config) {
            $contents[$id] = [
                'id' => $id,
                'revision' => $config['revision'] ?? null,
                'entityClass' => $config['entity_class'] ?? null,
                'defaultLayout' => $config['default_layout'] ?? null,
                'allowedLayouts' => $config['allowed_layouts'] ?? [],
                'saveCompiled' => (bool) ($config['save_compiled'] ?? true),
                'containers' => $this->summarizeContainers($config['containers'] ?? []),
                'extraFieldKeys' => array_keys($config['extra_fields'] ?? []),
                'indexingFieldKeys' => array_keys($config['indexing'] ?? []),
                'versionSeoFieldKeys' => array_keys($config['version_seo'] ?? []),
                'adminSections' => array_keys($config['admin'] ?? []),
                'metaKeys' => array_keys($config['meta'] ?? []),
                'seoRevisionMigrationScripts' => $config['seo_revision_migration_scripts'] ?? [],
            ];

            if ($includeRawConfig) {
                $contents[$id]['config'] = $this->sanitizer->sanitize($config);
            }
        }

        return $contents;
    }

    public function menus(bool $includeRawConfig): array
    {
        $menus = [];

        foreach ($this->cmsConfig->getMenus() as $id => $config) {
            $menus[$id] = [
                'id' => $id,
                'revision' => $config['revision'] ?? null,
                'renderTemplate' => $config['render_template'] ?? null,
            ];

            if ($includeRawConfig) {
                $menus[$id]['config'] = $this->sanitizer->sanitize($config);
            }
        }

        return $menus;
    }

    public function blocks(bool $includeRawConfig): array
    {
        $blocks = [];

        foreach ($this->cmsConfig->getBlocks() as $id => $config) {
            $blocks[$id] = [
                'id' => $id,
                'revision' => $config['revision'] ?? null,
                'enabled' => (bool) ($config['enabled'] ?? true),
                'renderTemplate' => $config['render_template'] ?? null,
                'formTemplate' => $config['form_template'] ?? null,
                'esi' => (bool) ($config['esi'] ?? true),
                'ajax' => (bool) ($config['ajax'] ?? false),
                'cacheType' => $config['cache_type'] ?? null,
                'cacheTtl' => $config['cache_ttl'] ?? null,
                'singleton' => (bool) ($config['singleton'] ?? true),
                'static' => (bool) ($config['static'] ?? false),
                'schedulable' => (bool) ($config['schedulable'] ?? false),
                'formFieldKeys' => array_keys($config['form_fields'] ?? []),
                'revisionMigrationScripts' => $config['revision_migration_scripts'] ?? [],
            ];

            if ($includeRawConfig) {
                $blocks[$id]['config'] = $this->sanitizer->sanitize($config);
            }
        }

        return $blocks;
    }

    public function plugins(): mixed
    {
        return $this->sanitizer->sanitize($this->cmsConfig->getRegisteredPlugins());
    }

    private function summarizeContainers(array $containers): array
    {
        $summary = [];

        foreach ($containers as $id => $config) {
            if (!is_array($config)) {
                continue;
            }

            $summary[$id] = [
                'required' => (bool) ($config['required'] ?? false),
                'allowedModules' => $config['allowed_modules'] ?? [],
            ];
        }

        return $summary;
    }
}
