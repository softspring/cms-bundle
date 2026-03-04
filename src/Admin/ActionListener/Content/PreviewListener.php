<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Content;

use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ViewEvent;

class PreviewListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'preview';

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_PREVIEW_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeUpdateHelperConfig', 0],
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
                ['onViewAddConfig', 0],
                ['onViewSetTemplate', 0],
                ['onViewAddEntities', 0],
                ['onViewAddLocalesAndVersion', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_PREVIEW_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onViewAddLocalesAndVersion(ViewEvent $event): void
    {
        $content = $event->getData()['content'];

        /* @deprecated */
        $event->getData()['enabledLocales'] = $content->getLocales();

        if ($event->getRequest()->query->get('version')) {
            $version = $content->getVersions()->filter(fn (ContentVersionInterface $version): bool => $version->getId() == $event->getRequest()->query->get('version'))->first();
        }

        $event->getData()['version'] = $version ?? $content->getLastVersion();
    }
}
