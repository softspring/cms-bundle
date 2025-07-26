<?php

namespace Softspring\CmsBundle\Admin\ActionListener\SectionVersion;

use Softspring\CmsBundle\Compiler\SectionVersionCompiler;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\InitializeEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RecompileListener extends AbstractSectionVersionListener
{
    protected const ACTION_NAME = 'version_recompile';

    public function __construct(
        SectionManagerInterface $sectionManager,
        SectionVersionManagerInterface $sectionVersionManager,
        RouteManagerInterface $routeManager,
        CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected SectionVersionCompiler $sectionVersionCompiler,
        protected bool $sectionRecompileEnabled,
    ) {
        parent::__construct($sectionManager, $sectionVersionManager, $routeManager, $cmsHelper, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_RECOMPILE_INITIALIZE => [
                ['onInitializeCheckEnabled', 20],
                ['onLoadSectionEntity', 9],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_RECOMPILE_LOAD_ENTITY => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_RECOMPILE_NOT_FOUND => [
                ['onNotFoundAddFlashAndRedirectToList', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_RECOMPILE_FOUND => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_RECOMPILE_APPLY => [
                ['onApplyRecompile', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_RECOMPILE_SUCCESS => [
                ['onSuccessAddFlash', 10],
                ['onSuccessRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_RECOMPILE_FAILURE => [
                ['onFailureAddFlash', 10],
                ['onFailureRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_RECOMPILE_EXCEPTION => [
                ['onExceptionAddFlash', 10],
                ['onExceptionRedirectBack', 0],
            ],
        ];
    }

    public function onInitializeCheckEnabled(InitializeEvent $event): void
    {
        if (!$this->sectionRecompileEnabled) {
            throw new NotFoundHttpException('Recompile is disabled');
        }
    }

    public function onApplyRecompile(ApplyEvent $event): void
    {
        /** @var SectionVersionInterface $entity */
        $entity = $event->getEntity();

        $entity->setKeep($event->getRequest()->attributes->get('recompile') ?: false);

        $entity->setCompileErrors(false);
        $entity->cleanCompiled();
        $this->sectionVersionCompiler->compileAll($entity);

        $this->sectionVersionManager->saveEntity($entity);

        $event->setApplied(true);
    }
}
