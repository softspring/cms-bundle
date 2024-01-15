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
    public function getAvailableLayouts(ContentInterface $content): array
    {
        $contentType = $this->cmsConfig->getContent($content);

        $layouts = $this->cmsConfig->getLayouts();

        if (!empty($contentType['allowed_layouts'])) {
            $availableLayouts = $contentType['allowed_layouts'];
        } else {
            $availableLayouts = array_keys($layouts);
        }

        foreach ($layouts as $layoutId => $layoutConfig) {
            if (!empty($layoutConfig['compatible_contents']) && !in_array($contentType['_id'], $layoutConfig['compatible_contents'])) {
                unset($availableLayouts[array_search($layoutId, $availableLayouts)]);
            }
        }

        return $availableLayouts;
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
