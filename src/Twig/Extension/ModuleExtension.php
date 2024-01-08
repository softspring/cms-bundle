<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Config\Exception\DisabledModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidSiteException;
use Softspring\CmsBundle\Render\ContentVersionRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @deprecated this is not used anymore, will be removed in next major version
 */
class ModuleExtension extends AbstractExtension
{
    protected ContentVersionRenderer $contentRender;

    public function __construct(ContentVersionRenderer $contentRender)
    {
        $this->contentRender = $contentRender;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_module', [$this, 'renderModule'], ['is_safe' => ['html']]),
        ];
    }

    public function renderModule(string $moduleId, array $data = []): string
    {
        try {
            return $this->contentRender->renderModuleById($moduleId, $data);
        } catch (DisabledModuleException|InvalidModuleException|InvalidSiteException $e) {
            // TODO LOG ERRORS AND FAIL ON DEVELOPMENT
            return '';
        }
    }
}
