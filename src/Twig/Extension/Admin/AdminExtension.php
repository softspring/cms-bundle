<?php

namespace Softspring\CmsBundle\Twig\Extension\Admin;

use Softspring\CmsBundle\Admin\Menu\MenuManager;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsSectionsPlugin\Manager\SectionManagerInterface;
use Softspring\CmsSectionsPlugin\Model\SectionInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AdminExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        protected CmsConfig $cmsConfig,
        protected RouterInterface $router,
        protected ContentManagerInterface $contentManager,
        protected MenuManager $menuManager,
        protected bool $contentRecompileEnabled,
        protected ?SectionManagerInterface $sectionManager,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'sfs_cms_admin_content_recompile_enabled' => $this->contentRecompileEnabled,
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sfs_cms_admin_content_url', [$this, 'getContentUrl']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_admin_content_url', [$this, 'getContentUrl']),
            new TwigFunction('sfs_cms_admin_content_menu', [$this, 'getContentMenu']),
            new TwigFunction('sfs_cms_admin_search_content_esi_calls', [$this, 'searchContentEsiCalls']),
            new TwigFunction('sfs_cms_admin_search_content_ajax_calls', [$this, 'searchContentAjaxCalls']),
        ];
    }

    public function getContentUrl(ContentInterface $content, string $action = 'details'): string
    {
        $contentType = $this->contentManager->getType($content);

        return $this->router->generate(sprintf('sfs_cms_admin_content_%s_%s', $contentType, $action), ['content' => $content]);
    }

    public function getContentMenu(string $current, ContentInterface $content): array
    {
        return $this->menuManager->getEntityMenu('content', $current, $content);
    }

    public function searchContentEsiCalls(string $content): array
    {
        $matches = [];
        preg_match_all('/<esi:include .*src="([^"]+)"\s?\/>/', $content, $matches);

        $esiCalls = [];

        /* @phpstan-ignore-next-line */
        foreach ($matches[1] ?? [] as $url) {
            $parsed = parse_url($url);

            $params = [];
            parse_str($parsed['query'], $params);

            if (isset($params['_path'])) {
                parse_str($params['_path'], $params['_path']);
            }

            $processed = [];

            switch ($params['_path']['_controller'] ?? false) {
                case 'Softspring\CmsBundle\Controller\BlockController::renderByType':
                    $processed['type'] = 'block';
                    $processed['block_type'] = $params['_path']['type'] ?? 'unknown';
                    $processed['block_config'] = $this->cmsConfig->getBlock("{$processed['block_type']}", false);
                    break;

                case 'Softspring\CmsSectionsPlugin\Controller\SectionController::renderById':
                    /* @var SectionInterface $section */
                    $processed['type'] = 'section';
                    $processed['section_id'] = $params['_path']['section'] ?? 'unknown';
                    $section = $this->sectionManager?->getRepository()->findOneById($processed['section_id']);
                    $processed['section_name'] = $section?->getName() ?? 'unknown';
                    $processed['section_ttl'] = $section?->getExtra('ttl') ?? 0;
                    break;

                case 'Softspring\CmsBundle\Controller\MenuController::renderByType':
                    $processed['type'] = 'menu';
                    $processed['menu_type'] = $params['_path']['type'] ?? 'unknown';
                    $processed['menu_config'] = $this->cmsConfig->getMenu("{$processed['menu_type']}", false);
                    break;

                default:
                    $processed['type'] = 'unknown';
            }

            $esiCalls[] = [
                'url' => $url,
                'parsed' => $parsed,
                'params' => $params,
                'processed' => $processed,
            ];
        }

        return $esiCalls;
    }

    public function searchContentAjaxCalls(string $content): array
    {
        $matches = [];
        preg_match_all('/<div.*data-sfs-cms-ajax=.*>/', $content, $matches);

        $ajaxCalls = [];

        /* @phpstan-ignore-next-line */
        foreach ($matches[0] ?? [] as $ajaxDiv) {
            // extract html attributes
            $attrs = [];
            preg_match_all('/data-([a-zA-Z0-9-_]+)="([^"]+)"/', $ajaxDiv, $attrMatches, PREG_SET_ORDER);
            foreach ($attrMatches as $attrMatch) {
                $attrs[$attrMatch[1]] = $attrMatch[2];
            }

            $ajaxCalls[] = [
                'processed' => [
                    'type' => $attrs['sfs-cms-ajax'] ?? 'unknown',
                    'block_id' => $attrs['sfs-cms-block-id'] ?? null,
                    'block_type' => $attrs['sfs-cms-block-type'] ?? null,
                    'url' => $attrs['href'] ?? null,
                    'section_id' => $attrs['sfs-cms-section-id'] ?? null,
                    'block_config' => isset($attrs['sfs-cms-block-type']) ? $this->cmsConfig->getBlock($attrs['sfs-cms-block-type'], false) : null,
                    'menu_config' => isset($attrs['sfs-cms-menu-type']) ? $this->cmsConfig->getMenu($attrs['sfs-cms-menu-type'], false) : null,
                ],
            ];
        }

        return $ajaxCalls;
    }
}
