<?php

namespace Softspring\CmsBundle\EventListener\Admin;

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
                return new RedirectResponse($this->router->generate(name: "sfs_cms_admin_content_{$configId}_versions", parameters: ['content' => $entity]));

            case 'version_info':
                if ($version) {
                    return new RedirectResponse($this->router->generate("sfs_cms_admin_content_{$configId}_version_info", ['content' => $entity, 'version' => $version]));
                }

                return new RedirectResponse($this->router->generate(name: "sfs_cms_admin_content_{$configId}_versions", parameters: ['content' => $entity]));

            case 'preview':
                if ($version) {
                    return new RedirectResponse($this->router->generate("sfs_cms_admin_content_{$configId}_preview_version", ['content' => $entity, 'version' => $version]));
                }

                return new RedirectResponse($this->router->generate("sfs_cms_admin_content_{$configId}_preview", ['content' => $entity]));

            default:
                return new RedirectResponse($this->router->generate("sfs_cms_admin_content_{$configId}_details", ['content' => $entity]));
        }
    }
}
