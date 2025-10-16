<?php

namespace Softspring\CmsBundle\Admin\ActionListener\ContentVersion;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\LoadEntityEvent;

class CleanupVersionsListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_cleanup';

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEANUP_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeUpdateHelperConfig', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEANUP_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEANUP_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFoundAddFlash', 5],
                ['onNotFoundRedirectToList', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEANUP_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEANUP_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEANUP_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccessAddFlash', 5],
                ['onEventRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEANUP_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEANUP_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onLoadEntity(LoadEntityEvent $event): void
    {
        $event->setEntity($event->getRequest()->attributes->get('content'));
    }

    public function onApply(ApplyEvent $event): void
    {
        $entity = $event->getEntity();

        foreach ($entity->getVersions() as $version) {
            if ($version->deleteOnCleanup()) {
                $entity->removeVersion($version); // TODO THIS SHOULD REMOVE VERSIONS
                $this->contentVersionManager->deleteEntity($version);
            }
        }
        $event->setApplied(true);
    }
}
