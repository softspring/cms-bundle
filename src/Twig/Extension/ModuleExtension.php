<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Config\Exception\DisabledModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidModuleException;
use Softspring\CmsBundle\Config\Exception\InvalidSiteException;
use Softspring\CmsBundle\Render\ContentRender;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModuleExtension extends AbstractExtension
{
    protected ContentRender $contentRender;

    public function __construct(ContentRender $contentRender)
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
