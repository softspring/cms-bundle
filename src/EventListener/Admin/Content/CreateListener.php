<?php

namespace Softspring\CmsBundle\EventListener\Admin\Content;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\CreateEntityEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\FormValidEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreateListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'create';

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_CREATE_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_CREATE_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onCreateEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_CREATE_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_CREATE_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_CREATE_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onValidModifyModel', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_CREATE_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_CREATE_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_CREATE_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_CREATE_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_CREATE_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_CREATE_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onCreateEntity(CreateEntityEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $entity = $this->contentManager->createEntity($contentConfig['_id']);
        $entity->addRoute($this->routeManager->createEntity());

        $event->setEntity($entity);
    }

    public function onFormPrepareResolve(FormPrepareEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $event->setType($contentConfig['admin']['create']['type']);
        $event->setFormOptions([
            'method' => 'POST',
            'content_config' => $contentConfig,
        ]);
    }

    public function onValidModifyModel(FormValidEvent $event): void
    {
        /** @var ContentInterface $entity */
        $entity = $event->getForm()->getData();

        $entity->getRoutes()->map(function (RouteInterface $route) use ($entity) {
            foreach ($entity->getSites() as $site) {
                $route->addSite($site);
            }
        });
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        if (empty($contentConfig['admin']['create']['success_redirect_to'])) {
            $contentConfig['admin']['create']['success_redirect_to'] = "sfs_cms_admin_content_{$contentConfig['_id']}_content";
        }

        $redirectUrl = $this->router->generate($contentConfig['admin']['create']['success_redirect_to'], ['content' => $event->getEntity()]);

        $event->setResponse(new RedirectResponse($redirectUrl));
    }
}
