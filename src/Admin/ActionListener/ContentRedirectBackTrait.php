<?php

namespace Softspring\CmsBundle\Admin\ActionListener;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

trait ContentRedirectBackTrait
{
    protected RouterInterface $router;

    /**
     * @noinspection PhpRouteMissingInspection
     */
    protected function redirectBack(string $configId, ContentInterface $entity, Request $request, ?ContentVersionInterface $version = null): RedirectResponse
    {
        switch ($request->query->get('back')) {
            case 'versions':
                $page = $request->query->get('page', 1);

                return new RedirectResponse($this->router->generate(name: "sfs_cms_admin_content_{$configId}_versions", parameters: ['content' => $entity->getId(), 'page' => $page]));

            case 'version_info':
                if ($version instanceof ContentVersionInterface) {
                    return new RedirectResponse($this->router->generate("sfs_cms_admin_content_{$configId}_version_info", ['content' => $entity->getId(), 'version' => $version->getId()]));
                }

                return new RedirectResponse($this->router->generate(name: "sfs_cms_admin_content_{$configId}_versions", parameters: ['content' => $entity->getId()]));

            case 'preview':
                if ($version instanceof ContentVersionInterface) {
                    return new RedirectResponse($this->router->generate("sfs_cms_admin_content_{$configId}_preview", ['content' => $entity->getId(), 'version' => $version->getId()]));
                }

                return new RedirectResponse($this->router->generate("sfs_cms_admin_content_{$configId}_preview", ['content' => $entity->getId()]));

            case 'details':
            default:
                return new RedirectResponse($this->router->generate("sfs_cms_admin_content_{$configId}_details", ['content' => $entity->getId()]));
        }
    }
}
