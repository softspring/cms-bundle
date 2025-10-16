<?php

namespace Softspring\CmsBundle\Admin\ActionListener\ContentVersion;

use Softspring\CmsBundle\Compiler\ContentVersionCompiler;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ClearCompiledListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_clear_compiled';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected ContentVersionCompiler $contentVersionCompiler,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsHelper, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEAR_COMPILED_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeUpdateHelperConfig', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEAR_COMPILED_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEAR_COMPILED_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFoundAddFlash', 5],
                ['onNotFoundRedirectToList', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEAR_COMPILED_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEAR_COMPILED_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEAR_COMPILED_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccessAddFlash', 5],
                ['onEventRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEAR_COMPILED_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailure', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CLEAR_COMPILED_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onExceptionAddFlash', 5],
                ['onEventRedirectBack', 0],
            ],
        ];
    }

    public function onApply(ApplyEvent $event): void
    {
        /** @var ContentVersionInterface $entity */
        $entity = $event->getEntity();

        $entity->setKeep($event->getRequest()->attributes->get('recompile') ?: false);

        $entity->cleanCompiled();

        $this->contentVersionManager->saveEntity($entity);

        $event->setApplied(true);
    }

    public function onFailure(FailureEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();

        $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.version_clear_compiled.failed_flash", ['%exception%' => $this->extractExceptionMessage($event->getException())], 'sfs_cms_contents');

        $content = $event->getRequest()->attributes->get('content');

        $event->setResponse($this->redirectBack($contentConfig['_id'], $content, $event->getRequest(), $version));
    }
}
