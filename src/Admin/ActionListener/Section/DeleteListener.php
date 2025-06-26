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
            SfsCmsEvents::ADMIN_SECTIONS_DELETE_NOT_FOUND => [
                ['onNotFoundAddFlashAndRedirectToList', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_FOUND => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_FORM_PREPARE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_FORM_INIT => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_FORM_VALID => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_APPLY => [],
            SfsCmsEvents::ADMIN_SECTIONS_DELETE_SUCCESS => [
                ['onSuccessAddFlash', 10],
                ['onSuccessRedirect', 0],
            ],
            SfsCmsEvents::ADMIN_SECTIONS_DELETE_FAILURE => [
                ['onFailureAddFormError', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_FORM_INVALID => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_VIEW => [],
            // SfsCmsEvents::ADMIN_SECTIONS_DELETE_EXCEPTION => [],
        ];
    }

    public function onSuccessRedirect(SuccessEvent $event): void
    {
        $event->setResponse(new RedirectResponse($this->router->generate('sfs_cms_admin_sections_list')));
    }
}
