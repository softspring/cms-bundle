<?php

namespace Softspring\CmsBundle\Admin\ActionListener\ContentVersion;

use Softspring\CmsBundle\Compiler\ContentVersionCompiler;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PublishListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_publish';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected ContentVersionCompiler $contentVersionCompiler,
        protected bool $autoCompileOnPublish,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailure', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onException', 0],
            ],
        ];
    }

    public function onApply(ApplyEvent $event): void
    {
        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();
        /** @var ContentInterface $content */
        $content = $event->getRequest()->attributes->get('content');

        if ($this->autoCompileOnPublish) {
            $this->contentVersionCompiler->compileAll($version, true);
        }

        $content->setPublishedVersion($version);
        $this->contentManager->saveEntity($content);

        $event->setApplied(true);
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        /** @var ContentInterface $content */
        $content = $event->getRequest()->attributes->get('content');

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.version_publish.success_flash", [], 'sfs_cms_contents');

        $event->setResponse($this->redirectBack($contentConfig['_id'], $content, $event->getRequest(), $event->getEntity()));
    }

    public function onFailure(FailureEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.version_publish.failed_flash", ['%exception%' => $event->getException()->getMessage()], 'sfs_cms_contents');

        $content = $event->getRequest()->attributes->get('content');

        $event->setResponse($this->redirectBack($contentConfig['_id'], $content, $event->getRequest()));
    }

    public function onException(FailureEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.version_publish.failed_flash", ['%exception%' => $event->getException()->getMessage()], 'sfs_cms_contents');

        $content = $event->getRequest()->attributes->get('content');

        $event->setResponse($this->redirectBack($contentConfig['_id'], $content, $event->getRequest()));
    }
}
