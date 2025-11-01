<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Helper\RoutingHelper;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouterExtension extends AbstractExtension
{
    public function __construct(
        protected RoutingHelper $routingHelper,
        protected UrlGenerator $urlGenerator,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_link_attr', [$this, 'generateLinkAttributes'], ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_resolve_request_from_url', [$this->routingHelper, 'resolveRequestFromUrl']),
            new TwigFunction('sfs_cms_url', [$this->routingHelper, 'generateUrl']),
            new TwigFunction('sfs_cms_path', [$this->routingHelper, 'generatePath']),
            new TwigFunction('sfs_cms_route_path_url', [$this->urlGenerator, 'getUrlFixed']), // TODO REVIEW THIS, check if it works with symfony native routes
            new TwigFunction('sfs_cms_route_path_path', [$this->urlGenerator, 'getPathFixed']), // TODO REVIEW THIS, check if it works with symfony native routes
            new TwigFunction('sfs_cms_route_attr', [$this->urlGenerator, 'getRouteAttributes'], ['is_safe' => ['html']]),
        ];
    }

    public function generateLinkAttributes(array $linkData, ?string $locale = null, $site = null): string
    {
        $attributes = [];
        $attributesString = '';

        switch ($linkData['type']) {
            case 'anchor':
                if (empty($linkData['anchor'])) {
                    return '';
                }
                $attributes['href'] = '#'.ltrim($linkData['anchor'], '#');
                break;

            case 'route':
                if (empty($linkData['route_name'])) {
                    return '';
                }
                $attributes['href'] = $this->routingHelper->generateUrl($linkData, $locale, $site);
                $attributesString .= $this->urlGenerator->getRouteAttributes($linkData);
                break;

            case 'url':
                if (empty($linkData['url'])) {
                    return '';
                }
                $attributes['href'] = $linkData['url'];
                break;
        }

        if ('_self' != $linkData['target']) {
            if ('custom' != $linkData['target']) {
                $attributes['target'] = $linkData['target'];
            } else {
                $attributes['target'] = $linkData['custom_target'];
            }
        }

        foreach ($attributes as $attr => $value) {
            $attributes[] = $attr.'="'.htmlentities($value).'"';
            unset($attributes[$attr]);
        }

        $attributesString && $attributes[] = $attributesString;

        return implode(' ', $attributes);
    }
}
