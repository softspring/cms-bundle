<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DeleteListener extends AbstractSectionListener
{
    protected const ACTION_NAME = 'delete';

    public static function getSubscribedEvents(): array
    {
        return [
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_INITIALIZE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_LOAD_ENTITY => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_NOT_FOUND => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_FOUND => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_FORM_PREPARE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_FORM_INIT => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_FORM_VALID => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_APPLY => [],
            SfsCmsEvents::ADMIN_SECTIONS_DELETE_SUCCESS => [
                ['onSuccess', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_FAILURE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_FORM_INVALID => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_VIEW => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_EXCEPTION => [],
        ];
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $this->flashNotifier->addTrans('success', 'admin_sections.delete.success_flash', [], 'sfs_cms_admin');
        $redirectUrl = $this->router->generate('sfs_cms_admin_sections_list');
        $event->setResponse(new RedirectResponse($redirectUrl));
    }
}
