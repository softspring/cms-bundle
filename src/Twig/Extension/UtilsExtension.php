<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Utils\HtmlValidator;
use Softspring\CmsSectionsPlugin\Manager\SectionManagerInterface;
use Softspring\CmsSectionsPlugin\Model\SectionInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class UtilsExtension extends AbstractExtension
{
    public function __construct(
        protected ContentManagerInterface $contentManager,
        protected CmsConfig $cmsConfig,
        protected ?SectionManagerInterface $sectionManager,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('base64_encode', 'base64_encode'),
            new TwigFilter('base64_decode', 'base64_decode'),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_search_content_esi_calls', [$this, 'searchContentEsiCalls']),
            new TwigFunction('sfs_cms_check_content_locales_and_routes', [$this, 'checkContentLocalesAndRoutes']),
            new TwigFunction('sfs_cms_validate_module_html', [HtmlValidator::class, 'validateModule']),
            new TwigFunction('sfs_cms_content_type', [$this->contentManager, 'getType']),
            new TwigFunction('sfs_cms_render_ajax', [$this, 'renderAjax'], ['is_safe' => ['html']]),
        ];
    }

    public function renderAjax(string $url, array $containerAttrs = []): string
    {
        $divId = 'a'.rand(100000, 999999);
        $containerAttrs['id'] = $divId;
        $containerAttrs['data-href'] = $url;

        $attrs = implode(' ', array_map(fn ($k, $v) => sprintf('%s="%s"', htmlspecialchars($k, ENT_QUOTES), htmlspecialchars($v, ENT_QUOTES)), array_keys($containerAttrs), $containerAttrs));

        return <<<AJAX
<div $attrs>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            const ajaxDiv = document.getElementById('$divId');
            fetch(ajaxDiv.getAttribute('data-href'))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response;
                })
                .then(response => response.text())
                .then(html => {
                    ajaxDiv.outerHTML = html;
                })
                .catch(error => console.error('Error loading ajax content:', error));
        });
    </script>
</div>
AJAX;
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

    public function checkContentLocalesAndRoutes(ContentInterface $content): array
    {
        $locales = $content->getLocales();

        $routesLocales = [];
        foreach ($content->getRoutes() as $route) {
            foreach ($route->getPaths() as $path) {
                if (!$path->getLocale() || in_array($path->getLocale(), $routesLocales)) {
                    continue;
                }
                $routesLocales[] = $path->getLocale();
            }
        }
        $routesLocales = array_unique($routesLocales);

        return [
            'locales' => $locales,
            'routes_locales' => $routesLocales,
            'missing_route_locales' => array_diff($locales, $routesLocales),
        ];
    }
}
