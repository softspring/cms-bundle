<?php

namespace Softspring\CmsBundle\Admin\ActionListener\ContentVersion;

use Softspring\CmsBundle\Compiler\CompileAllException;
use Softspring\CmsBundle\Compiler\CompileException;
use Softspring\CmsBundle\Compiler\ContentVersionCompiler;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PublishListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_publish';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected ContentVersionCompiler $contentVersionCompiler,
        protected bool $contentAutoCompileOnPublish,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsHelper, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeUpdateHelperConfig', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFoundAddFlash', 0],
                ['onNotFoundRedirectToList', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApplyAutoCompile', 0],
                ['onApplySetPublished', 0],
                ['onEventSaveVersion', 0],
                ['onEventSaveContent', 0],
                ['onApplySetApplied', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccessAddFlash', 0],
                ['onEventRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventSaveVersion', 0], // save errors on failure
                ['onFailureAddFlash', 0],
                ['onEventRedirectBack', 0], // after process redirect back
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_PUBLISH_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onExceptionAddFlash', 0],
                ['onEventRedirectBack', 0],
            ],
        ];
    }

    /**
     * @throws CompileAllException
     */
    public function onApplyAutoCompile(ApplyEvent $event): void
    {
        if (!$this->contentAutoCompileOnPublish) {
            return;
        }

        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();
        $version->cleanCompiled();
        $this->contentVersionCompiler->compileAll($version);

        if ($version->hasCompileErrors()) {
            throw new CompileException('Error compiling content version');
        }
    }

    public function onApplySetPublished(ApplyEvent $event): void
    {
        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();
        $version->setKeep(true); // Keep the version after publishing
        $version->getContent()->setPublishedVersion($version);
    }
}
