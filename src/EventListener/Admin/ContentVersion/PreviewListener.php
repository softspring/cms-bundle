<?php

namespace Softspring\CmsBundle\EventListener\Admin\ContentVersion;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Render\ContentRender;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\EntityFoundEvent;
use Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PreviewListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_preview';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected SiteManagerInterface $siteManager,
        protected ContentRender $contentRender,
        protected ?WebDebugToolbarListener $webDebugToolbarListener = null,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PREVIEW_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PREVIEW_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PREVIEW_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PREVIEW_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFoundShowContent', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PREVIEW_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onFoundShowContent(EntityFoundEvent $event): void
    {
        $this->webDebugToolbarListener && $this->webDebugToolbarListener->setMode(WebDebugToolbarListener::DISABLED);

        $request = $event->getRequest();

        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();
        $entity = $version->getContent();

        $request->setLocale($request->query->get('_locale', 'en'));

        $site = $request->query->has('_site') ? $this->siteManager->getRepository()->findOneById($request->query->get('_site')) : $entity->getSites()->first();
        $request->attributes->set('_site', "$site");
        $request->attributes->set('_sfs_cms_site', $site);

        $request->attributes->set('_cms_preview', true);

        $event->setResponse(new Response($this->contentRender->render($version)));
    }
}
