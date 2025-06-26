<?php

namespace Softspring\CmsBundle\Admin\ActionListener;

use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

trait SectionRedirectBackTrait
{
    protected RouterInterface $router;

    protected function redirectBack(SectionInterface $entity, Request $request, ?SectionVersionInterface $version = null): RedirectResponse
    {
        switch ($request->query->get('back')) {
            case 'versions':
                $page = $request->query->get('page', 1);

                return new RedirectResponse($this->router->generate(name: 'sfs_cms_admin_sections_versions', parameters: ['section' => $entity, 'page' => $page]));

            case 'version_info':
                if ($version) {
                    return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_version_info', ['section' => $entity, 'version' => $version]));
                }

                return new RedirectResponse($this->router->generate(name: 'sfs_cms_admin_sections_versions', parameters: ['section' => $entity]));

            case 'preview':
                if ($version) {
                    return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_preview', ['section' => $entity, 'version' => $version]));
                }

                return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_preview', ['section' => $entity]));

            case 'details':
            default:
                return new RedirectResponse($this->router->generate('sfs_cms_admin_sections_details', ['section' => $entity]));
        }
    }
}
