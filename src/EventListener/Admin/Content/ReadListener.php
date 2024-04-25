<?php

namespace Softspring\CmsBundle\EventListener\Admin\Content;

use DateTime;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\LoadEntityEvent;
use Softspring\Component\CrudlController\Event\NotFoundEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ReadListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'read';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected bool $contentCacheLastModifiedEnabled,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_READ_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeIsGranted', 0],
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
                ['onView', 0],
                ['onViewAddCacheAlert', -10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_READ_EXCEPTION => [
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
        $event->getData()['entity'] = $event->getData()['content'];
        $event->getData()['entityLatestVersions'] = $this->contentVersionManager->getLatestVersions($event->getData()['content'], 3);
        parent::onView($event);
        $event->getData()['contentCacheLastModifiedEnabled'] = $this->contentCacheLastModifiedEnabled;
    }

    public function onViewAddCacheAlert(ViewEvent $event): void
    {
        /** @var ContentInterface $content */
        $content = $event->getData()['content'];

        if ($this->contentCacheLastModifiedEnabled) {
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
            return $route->getPaths()->map(function (RoutePathInterface $path) {
                return $path->getCacheTtl();
            })->filter(function ($ttl) {
                return null !== $ttl;
            })->toArray();
        })->toArray());

        $maxRouteTtl = count($routeTtls) ? max($routeTtls) : null;
        $minRouteTtl = count($routeTtls) ? min($routeTtls) : null;

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
