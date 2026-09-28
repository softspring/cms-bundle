<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Content;

use DateTime;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ReadListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'read';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected string $contentCacheType,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsHelper, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_READ_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeUpdateHelperConfig', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_READ_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_READ_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_READ_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_READ_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onViewAddConfig', 0],
                ['onViewSetTemplate', 0],
                ['onViewAddEntities', 0],
                ['onViewAddExtra', 0],
                ['onViewAddCacheAlert', -10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_READ_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onViewAddExtra(ViewEvent $event): void
    {
        $latestVersions = $this->contentVersionManager->getLatestVersions($event->getData()['content'], 3);
        $publishedVersion = $event->getData()['content']->getPublishedVersion();

        if ($publishedVersion && !$latestVersions->contains($publishedVersion)) {
            // remove last
            $latestVersions->offsetUnset($latestVersions->count() - 1);
            // add published at the end
            $latestVersions->add($publishedVersion);
        }

        $event->getData()['entityLatestVersions'] = $latestVersions;
        $event->getData()['contentCacheLastModifiedEnabled'] = 'last_modified' === $this->contentCacheType;
    }

    public function onViewAddCacheAlert(ViewEvent $event): void
    {
        /** @var ContentInterface $content */
        $content = $event->getData()['content'];

        if ('last_modified' === $this->contentCacheType) {
            // if cache lastModified is enabled, no need to check cache alert
            return;
        }

        if (!$content->getLastModified()) {
            // no published version, no cache
            return;
        }

        $current = new DateTime();
        //        $publishedAt = $content->getPublishedVersion()->getPublishedAt();
        $publishedAt = $content->getLastModified();

        $currentTimestamp = $current->format('U');
        $publishedTimestamp = $publishedAt->format('U');

        $routeTtls = call_user_func_array('array_merge', $content->getRoutes()->map(function (RouteInterface $route) {
            return $route->getPaths()->map(function (RoutePathInterface $path): ?int {
                return $path->getCacheTtl();
            })->filter(function ($ttl): bool {
                return null !== $ttl;
            })->toArray();
        })->toArray());

        $maxRouteTtl = count($routeTtls) > 0 ? max($routeTtls) : null;
        $minRouteTtl = count($routeTtls) > 0 ? min($routeTtls) : null;

        if ($currentTimestamp - $publishedTimestamp > $maxRouteTtl) {
            // published version is too old, no cache
            return;
        }

        $event->getData()['cache_alert'] = [
            'maxRouteTtl' => $maxRouteTtl,
            'minRouteTtl' => $minRouteTtl,
            'currentDatetime' => $current,
            'publishedDatetime' => $publishedAt,
            'waitTime' => $maxRouteTtl - ($currentTimestamp - $publishedTimestamp),
        ];
    }
}
