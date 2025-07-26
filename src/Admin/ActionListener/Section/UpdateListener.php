<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UpdateListener extends AbstractSectionListener
{
    protected const ACTION_NAME = 'update';

    public function __construct(
        SectionManagerInterface $sectionManager,
        SectionVersionManagerInterface $sectionVersionManager,
        RouteManagerInterface $routeManager,
        CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected TranslatableContext $translatableContext,
    ) {
        parent::__construct($sectionManager, $sectionVersionManager, $routeManager, $cmsHelper, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // SfsCmsEvents::ADMIN_SECTIONS_UPDATE_INITIALIZE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_UPDATE_LOAD_ENTITY => [],
            SfsCmsEvents::ADMIN_SECTIONS_UPDATE_NOT_FOUND => [
                ['onNotFoundAddFlashAndRedirectToList', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_UPDATE_FOUND => [],
            // SfsCmsEvents::ADMIN_SECTIONS_UPDATE_FORM_PREPARE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_UPDATE_FORM_INIT => [],
            // SfsCmsEvents::ADMIN_SECTIONS_UPDATE_FORM_VALID => [],
            // SfsCmsEvents::ADMIN_SECTIONS_UPDATE_APPLY => [],
            SfsCmsEvents::ADMIN_SECTIONS_UPDATE_SUCCESS => [
                ['onSuccessAddFlash', 10],
                ['onSuccessRedirect', 0],
            ],
            SfsCmsEvents::ADMIN_SECTIONS_UPDATE_FAILURE => [
                ['onFailureAddFormError', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_UPDATE_FORM_INVALID => [],
            // SfsCmsEvents::ADMIN_SECTIONS_UPDATE_VIEW => [],
            // SfsCmsEvents::ADMIN_SECTIONS_UPDATE_EXCEPTION => [],
        ];
    }

    public function onSuccessRedirect(SuccessEvent $event): void
    {
        $event->setResponse(new RedirectResponse($this->router->generate('sfs_cms_admin_sections_details', ['section' => $event->getEntity()])));
    }
}
