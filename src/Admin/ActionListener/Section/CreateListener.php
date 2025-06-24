<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\Form\FormError;
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
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_SECTIONS_CREATE_FAILURE => [
                ['onFailureShowAlert', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_FORM_INVALID => [],
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_VIEW => [],
            // SfsCmsEvents::ADMIN_SECTIONS_CREATE_EXCEPTION => [],
        ];
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $this->flashNotifier->addTrans('success', 'admin_sections.create_success_flash', [], 'sfs_cms_admin');

        $redirectUrl = $this->router->generate('sfs_cms_admin_sections_section', ['section' => $event->getEntity()]);

        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    public function onFailureShowAlert(FailureEvent $event): void
    {
        $exception = $event->getException();
        $event->getForm()->addError(new FormError($this->extractExceptionMessage($exception)));
    }
}
