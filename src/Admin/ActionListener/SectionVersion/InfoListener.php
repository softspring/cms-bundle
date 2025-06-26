<?php

namespace Softspring\CmsBundle\Admin\ActionListener\SectionVersion;

use Softspring\CmsBundle\Compiler\SectionVersionCompiler;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class InfoListener extends AbstractSectionVersionListener
{
    protected const ACTION_NAME = 'version_info';

    public function __construct(
        SectionManagerInterface $sectionManager,
        SectionVersionManagerInterface $sectionVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected SectionVersionCompiler $sectionVersionCompiler,
        protected bool $sectionSaveCompiled,
    ) {
        parent::__construct($sectionManager, $sectionVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_INITIALIZE => [
                ['onLoadSectionEntity', 9],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_LOAD_ENTITY => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_NOT_FOUND => [
                ['onNotFoundAddFlashAndRedirectToList', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_FOUND => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_FORM_PREPARE => [
                ['onFormPrepareResolve', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_FORM_INIT => [],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_FORM_VALID => [],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_APPLY => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_SUCCESS => [
                ['onSuccess', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_FAILURE => [],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_FORM_INVALID => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_VIEW => [
                ['onViewAddEntities', 10],
                ['onView', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_INFO_EXCEPTION => [],
        ];
    }

    public function onFormPrepareResolve(FormPrepareEvent $event): void
    {
        $event->setFormOptions([
            'section' => $event->getRequest()->attributes->get('section'),
        ]);
    }

    public function onView(ViewEvent $event): void
    {
        $version = $event->getRequest()->attributes->get('version');
        $event->getData()['version_entity'] = $version;
        $event->getData()['section_can_be_compiled'] = $this->sectionSaveCompiled;
        $event->getData()['section_can_compile_modules'] = $this->sectionSaveCompiled;
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $version = $event->getEntity();
        $section = $version->getSection();

        $url = $this->router->generate('sfs_cms_admin_sections_version_info', ['section' => $section, 'version' => $version]);
        $event->setResponse(new RedirectResponse($url));
    }
}
