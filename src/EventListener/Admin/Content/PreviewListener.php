<?php

namespace Softspring\CmsBundle\EventListener\Admin\Content;

use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\LoadEntityEvent;
use Softspring\Component\CrudlController\Event\NotFoundEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PreviewListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'preview';

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_PREVIEW_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_PREVIEW_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_PREVIEW_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_PREVIEW_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_PREVIEW_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_PREVIEW_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onLoadEntity(LoadEntityEvent $event): void
    {
        $contentId = $event->getRequest()->attributes->get('content');
        $contentConfig = $event->getRequest()->attributes->get('_content_config');
        $entity = $this->contentManager->getRepository($contentConfig['_id'])->findOneBy(['id' => $contentId]);
        $event->setEntity($entity);
        $event->setNotFound(!$entity);
    }

    public function onNotFound(NotFoundEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('warning', "admin_{$contentConfig['_id']}.entity_not_found_flash", [], 'sfs_cms_contents');
        $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_list");
        $event->setResponse(new RedirectResponse($url));
    }

    public function onView(ViewEvent $event): void
    {
        $content = $event->getData()['content'];
        $event->getData()['entity'] = $content;
        //        $event->getData()['entityLatestVersions'] = $this->contentVersionManager->getLatestVersions($event->getData()['content'], 3);

        parent::onView($event);

        /* @deprecated */
        $event->getData()['enabledLocales'] = $content->getLocales();
        $event->getData()['content_entity'] = $content;

        if ($event->getRequest()->query->get('version')) {
            $version = $content->getVersions()->filter(fn (ContentVersionInterface $version) => $version->getId() == $event->getRequest()->query->get('version'))->first();
        }

        $event->getData()['version'] = $version ?? $content->getVersions()->first();
    }
}
