<?php

namespace Softspring\CmsBundle\EventListener\Admin\ContentVersion;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Render\ContentVersionCompiler;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RecompileListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_recompile';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected ContentVersionCompiler $contentVersionCompiler,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailure', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onApply(ApplyEvent $event): void
    {
        /** @var ContentVersionInterface $entity */
        $entity = $event->getEntity();

        $entity->setKeep($event->getRequest()->attributes->get('recompile') ?: false);

        $this->contentVersionCompiler->compileAll($entity);

        $this->contentVersionManager->saveEntity($entity);

        $event->setApplied(true);
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.version_recompile.success_flash", [], 'sfs_cms_contents');

        $content = $event->getRequest()->attributes->get('content');

        $event->setResponse($this->redirectBack($contentConfig['_id'], $content, $event->getRequest(), $version));
    }

    public function onFailure(FailureEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.version_recompile.failed_flash", ['%exception%' => $event->getException()->getMessage()], 'sfs_cms_contents');

        $content = $event->getRequest()->attributes->get('content');

        $event->setResponse($this->redirectBack($contentConfig['_id'], $content, $event->getRequest()));
    }
}
