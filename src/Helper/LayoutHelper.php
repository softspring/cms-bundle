<?php

namespace Softspring\CmsBundle\Helper;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Config\Exception\InvalidLayoutException;
use Softspring\CmsBundle\Model\ContentInterface;

class LayoutHelper
{
    public function __construct(protected CmsConfig $cmsConfig)
    {
    }

    /**
     * @throws InvalidContentException
     */
    public function getAvailableLayouts(ContentInterface $content, ?string $currentLayout = null): array
    {
        $contentType = $this->cmsConfig->getContent($content);

        $layouts = $this->cmsConfig->getLayouts();

        $availableLayouts = empty($contentType['allowed_layouts']) ? array_keys($layouts) : $contentType['allowed_layouts'];

        foreach ($layouts as $layoutId => $layoutConfig) {
            if (!empty($layoutConfig['compatible_contents']) && !in_array($contentType['_id'], $layoutConfig['compatible_contents'])) {
                if (false !== $layoutIndex = array_search($layoutId, $availableLayouts)) {
                    unset($availableLayouts[$layoutIndex]);
                }
            }

            if (false === $layoutConfig['enabled'] && $layoutId !== $currentLayout) {
                if (false !== $layoutIndex = array_search($layoutId, $availableLayouts)) {
                    unset($availableLayouts[$layoutIndex]);
                }
            }
        }

        if ($currentLayout && isset($layouts[$currentLayout]) && !in_array($currentLayout, $availableLayouts)) {
            $availableLayouts[] = $currentLayout;
        }

        return array_values($availableLayouts);
    }

    /**
     * @throws InvalidLayoutException
     * @throws InvalidContentException
     */
    public function getDefaultLayout(ContentInterface $content): string
    {
        $availableLayouts = $this->getAvailableLayouts($content);
        $contentType = $this->cmsConfig->getContent($content);
        $defaultLayout = $contentType['default_layout'];

        $layout = $this->cmsConfig->getLayout($defaultLayout, false);

        if (!in_array($defaultLayout, $availableLayouts) || !$layout) {
            $defaultLayout = $availableLayouts[0];
        }

        return $defaultLayout;
    }
}
