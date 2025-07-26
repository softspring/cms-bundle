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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ClearCompiledListener extends AbstractSectionVersionListener
{
    protected const ACTION_NAME = 'version_clear_compiled';

    public function __construct(
        SectionManagerInterface $sectionManager,
        SectionVersionManagerInterface $sectionVersionManager,
        RouteManagerInterface $routeManager,
        CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected SectionVersionCompiler $sectionVersionCompiler,
    ) {
        parent::__construct($sectionManager, $sectionVersionManager, $routeManager, $cmsHelper, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CLEAR_COMPILED_INITIALIZE => [
                ['onLoadSectionEntity', 9],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_CLEAR_COMPILED_LOAD_ENTITY => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CLEAR_COMPILED_NOT_FOUND => [
                ['onNotFoundAddFlashAndRedirectToList', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_CLEAR_COMPILED_FOUND => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CLEAR_COMPILED_APPLY => [
                ['onApplyClearCompiled', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CLEAR_COMPILED_SUCCESS => [
                ['onSuccessAddFlash', 10],
                ['onSuccessRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CLEAR_COMPILED_FAILURE => [
                ['onFailureAddFlash', 10],
                ['onFailureRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_CLEAR_COMPILED_EXCEPTION => [
                ['onExceptionAddFlash', 10],
                ['onExceptionRedirectBack', 0],
            ],
        ];
    }

    public function onApplyClearCompiled(ApplyEvent $event): void
    {
        /** @var SectionVersionInterface $entity */
        $entity = $event->getEntity();

        $entity->setKeep($event->getRequest()->attributes->get('recompile') ?: false);

        $entity->cleanCompiled();

        $this->sectionVersionManager->saveEntity($entity);

        $event->setApplied(true);
    }
}
