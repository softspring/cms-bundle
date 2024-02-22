<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Utils\HtmlValidator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UtilsExtension extends AbstractExtension
{
    public function __construct(protected ContentManagerInterface $contentManager)
    {
    }

    public function getFunctions(): array
    {

        return [
            new TwigFunction('sfs_cms_search_content_esi_calls', [$this, 'searchContentEsiCalls']),
            new TwigFunction('sfs_cms_check_content_locales_and_routes', [$this, 'checkContentLocalesAndRoutes']),
            new TwigFunction('sfs_cms_validate_module_html', [HtmlValidator::class, 'validateModule']),
            new TwigFunction('sfs_cms_content_type', [$this->contentManager, 'getType']),
        ];
    }

    public function searchContentEsiCalls(string $content): array
    {
        $matches = [];
        preg_match_all('/<esi:include src="([^"]+)"\s?\/>/', $content, $matches);

        $esiCalls = [];

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
                    break;

                case 'Softspring\CmsBundle\Controller\MenuController::renderByType':
                    $processed['type'] = 'menu';
                    $processed['menu_type'] = $params['_path']['type'] ?? 'unknown';
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
