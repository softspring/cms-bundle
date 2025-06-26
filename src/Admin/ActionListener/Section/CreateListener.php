<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreateListener extends AbstractSectionListener
{
    protected const ACTION_NAME = 'create';

    public static function getSubscribedEvents(): array
    {
        return [
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_INITIALIZE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_ENTITY => [],
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_FORM_PREPARE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_FORM_INIT => [],
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_FORM_VALID => [],
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_APPLY => [],
            SfsCmsEvents::ADMIN_SECTIONS_CREATE_SUCCESS => [
                ['onSuccessAddFlash', 10],
                ['onSuccessRedirect', 0],
            ],
            SfsCmsEvents::ADMIN_SECTIONS_CREATE_FAILURE => [
                ['onFailureAddFormError', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_FORM_INVALID => [],
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_VIEW => [],
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_EXCEPTION => [],
        ];
    }

    public function onSuccessRedirect(SuccessEvent $event): void
    {
        $event->setResponse(new RedirectResponse($this->router->generate('sfs_cms_admin_sections_content', ['section' => $event->getEntity()])));
    }
}
