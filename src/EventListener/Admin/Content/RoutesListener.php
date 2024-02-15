<?php

namespace Softspring\CmsBundle\EventListener\Admin\Content;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RoutesListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'routes';

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFilterFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_ROUTES_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onFilterFormPrepareResolve(FormPrepareEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $event->setType($contentConfig['admin']['routes']['type']);
        $event->setFormOptions([
            'method' => 'POST',
            'content_config' => $contentConfig,
            'content' => $event->getEntity(),
        ]);
    }

    public function onApply(ApplyEvent $event): void
    {
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.routes.success_flash", [], 'sfs_cms_contents');

        if (empty($contentConfig['admin']['routes']['success_redirect_to'])) {
            /** @var ContentInterface $entity */
            $entity = $event->getEntity();
            $event->setResponse($this->redirectBack($contentConfig['_id'], $entity, $event->getRequest()));
        } else {
            $redirectUrl = $this->router->generate($contentConfig['admin']['routes']['success_redirect_to'], ['content' => $event->getEntity()]);

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }

    public function onView(ViewEvent $event): void
    {
        $event->getData()['entity'] = $event->getData()['content'];
        parent::onView($event);
    }
}
