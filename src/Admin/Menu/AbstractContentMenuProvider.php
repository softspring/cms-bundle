<?php

namespace Softspring\CmsBundle\Admin\Menu;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractContentMenuProvider implements ContentMenuProviderInterface
{
    public function __construct(
        protected CmsConfig $cmsConfig,
        protected ContentManagerInterface $contentManager,
        protected RouterInterface $router,
        protected TranslatorInterface $translator,
        protected AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    protected function getMenuItem(string $id, string $current, ContentInterface $content, string $contentType, array $contentConfig, ?string $configKey = null): MenuItem
    {
        $text = $this->translator->trans("admin_{$contentType}.tabs_menu.$id", [], 'sfs_cms_contents');
        $url = $this->router->generate("sfs_cms_admin_content_{$contentType}_{$id}", ['content' => $content]);
        $active = $current == $id;
        $isGranted = $contentConfig['admin'][$configKey ?? $id]['is_granted'] ?? null;
        $disabled = ('#' === $url) || ($isGranted && !$this->authorizationChecker->isGranted($isGranted, $content));

        return new MenuItem($id, $text, $url, $active, $disabled);
    }

    /**
     * @throws InvalidContentException
     * @throws \RuntimeException
     */
    protected function getContent(array $context): array
    {
        if (!isset($context['content']) || !$context['content'] instanceof ContentInterface) {
            throw new \RuntimeException('ContentMenuProvider requires a content instance in context');
        }

        $content = $context['content'];
        $contentType = $this->contentManager->getType($content);
        $contentConfig = $this->cmsConfig->getContent($contentType);

        return [$content, $contentType, $contentConfig];
    }
}
