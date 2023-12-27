<?php

namespace Softspring\CmsBundle\EventListener\Admin\Content;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UnpublishListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'unpublish';

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_UNPUBLISH_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UNPUBLISH_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UNPUBLISH_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UNPUBLISH_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UNPUBLISH_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UNPUBLISH_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UNPUBLISH_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailure', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UNPUBLISH_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onApply(ApplyEvent $event): void
    {
        $entity = $event->getEntity();
        $entity->setPublishedVersion(null);
        $this->contentManager->saveEntity($entity);
        $event->setApplied(true);
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.unpublish.has_been_unpublished_flash", [], 'sfs_cms_contents');

        if (empty($contentConfig['admin']['unpublish']['success_redirect_to'])) {
            /** @var ContentInterface $entity */
            $entity = $event->getEntity();
            $event->setResponse($this->redirectBack($contentConfig['_id'], $entity, $event->getRequest()));
        } else {
            $redirectUrl = $this->router->generate($contentConfig['admin']['unpublish']['success_redirect_to'], ['content' => $event->getEntity()]);

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }

    public function onFailure(FailureEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.unpublish.failure_flash", ['%exception%' => $event->getException()->getMessage()], 'sfs_cms_contents');

        $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_list");

        $event->setResponse(new RedirectResponse($url));
    }
}
