<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Utils\HtmlValidator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class UtilsExtension extends AbstractExtension
{
    public function __construct(
        protected ContentManagerInterface $contentManager,
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
