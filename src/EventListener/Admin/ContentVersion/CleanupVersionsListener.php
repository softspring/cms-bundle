<?php

namespace Softspring\CmsBundle\EventListener\Admin\ContentVersion;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\LoadEntityEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEANUP_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEANUP_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
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
                ['onSuccess', 0],
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

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onSuccess(SuccessEvent $event): void
    {
        $request = $event->getRequest();
        $contentConfig = $request->attributes->get('_content_config');
        $content = $request->attributes->get('content');

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.version_cleanup.success_flash", [], 'sfs_cms_contents');

        if ($this->getOption($request, 'success_redirect_to')) {
            $url = $this->router->generate($this->getOption($request, 'success_redirect_to'), ['content' => $content]);
        } else {
            $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_versions", ['content' => $content]);
        }

        $event->setResponse(new RedirectResponse($url));
    }
}
