<?php

namespace Softspring\CmsBundle\Admin\ActionListener\SectionVersion;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DeleteListener extends AbstractSectionVersionListener
{
    protected const ACTION_NAME = 'version_delete';

    public function __construct(
        SectionManagerInterface $sectionManager,
        SectionVersionManagerInterface $sectionVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected TranslatableContext $translatableContext,
    ) {
        parent::__construct($sectionManager, $sectionVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_INITIALIZE => [
                ['onLoadSectionEntity', 9],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_LOAD_ENTITY => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_NOT_FOUND => [
                ['onNotFoundAddFlashAndRedirectToList', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_FOUND => [],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_FORM_PREPARE => [],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_FORM_INIT => [],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_FORM_VALID => [],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_APPLY => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_SUCCESS => [
                ['onSuccessAddFlash', 10],
                ['onSuccessRedirectBack', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_FAILURE => [
                ['onFailureAddFlash', 10],
                ['onFailureRedirectBack', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_FORM_INVALID => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_VIEW => [
                ['onViewAddEntities', 10],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_DELETE_EXCEPTION => [],
        ];
    }

    public function onSuccessRedirectBack(SuccessEvent $event): void
    {
        $event->setResponse($this->redirectBack($event->getRequest()->attributes->get('section'), $event->getRequest()));
    }
}
