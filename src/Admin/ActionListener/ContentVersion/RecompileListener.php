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
use Softspring\Component\CrudlController\Event\InitializeEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RecompileListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_recompile';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected ContentVersionCompiler $contentVersionCompiler,
        protected bool $contentRecompileEnabled,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsHelper, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_INITIALIZE => [
                ['onInitializeCheckEnabled', 20],
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeUpdateHelperConfig', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFoundAddFlash', 0],
                ['onNotFoundRedirectToList', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApplyCompile', 0],
                ['onEventSaveVersion', 0],
                ['onApplySetApplied', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccessAddFlash', 0],
                ['onEventRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventSaveVersion', 0], // save errors on failure
                ['onFailureAddFlash', 0],
                ['onEventRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_RECOMPILE_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onExceptionAddFlash', 0],
                ['onEventRedirectBack', 0],
            ],
        ];
    }

    public function onInitializeCheckEnabled(InitializeEvent $event): void
    {
        if (!$this->contentRecompileEnabled) {
            throw new NotFoundHttpException('Recompile is disabled');
        }
    }

    /**
     * @throws CompileAllException
     */
    public function onApplyCompile(ApplyEvent $event): void
    {
        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();
        $version->cleanCompiled();
        $this->contentVersionCompiler->compileAll($version);

        if ($version->hasCompileErrors()) {
            throw new CompileException('Error compiling content version');
        }
    }
}
