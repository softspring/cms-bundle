<?php

namespace Softspring\CmsBundle\Admin\ActionListener\SectionVersion;

use Softspring\CmsBundle\Compiler\CompileException;
use Softspring\CmsBundle\Compiler\SectionVersionCompiler;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\EntityFoundEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PreviewListener extends AbstractSectionVersionListener
{
    protected const ACTION_NAME = 'version_preview';

    public function __construct(
        SectionManagerInterface $sectionManager,
        SectionVersionManagerInterface $sectionVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        protected CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected SiteManagerInterface $siteManager,
        protected SectionVersionCompiler $sectionVersionCompiler,
        protected ?WebDebugToolbarListener $webDebugToolbarListener = null,
    ) {
        parent::__construct($sectionManager, $sectionVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_PREVIEW_INITIALIZE => [
                ['onLoadSectionEntity', 9],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_PREVIEW_LOAD_ENTITY => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_PREVIEW_NOT_FOUND => [
                ['onNotFoundAddFlashAndRedirectToList', 0],
            ],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_PREVIEW_FOUND => [
                ['onFoundShowSection', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTION_VERSIONS_PREVIEW_EXCEPTION => [],
            SfsCmsEvents::ADMIN_SECTION_VERSIONS_PREVIEW_VIEW => [
                ['onViewAddEntities', 10],
                ['onView', 0],
            ],
        ];
    }

    /**
     * @throws CompileException
     */
    public function onFoundShowSection(EntityFoundEvent $event): void
    {
        $this->webDebugToolbarListener && $this->webDebugToolbarListener->setMode(WebDebugToolbarListener::DISABLED);
        //        $event->setResponse($this->getContentResponse($event->getRequest(), $event->getEntity()));
    }

    public function onView(ViewEvent $event): void
    {
        $version = $event->getRequest()->attributes->get('version');

        $event->getData()['content'] = $this->getContentResponse($event->getRequest(), $version)->getContent();
    }

    protected function getContentResponse(Request $request, SectionVersionInterface $version): Response
    {
        $this->webDebugToolbarListener && $this->webDebugToolbarListener->setMode(WebDebugToolbarListener::DISABLED);

        $request->setLocale($request->query->get('_locale', $request->getLocale()));

        $site = $request->query->has('_site') ? $this->siteManager->getRepository()->findOneById($request->query->get('_site')) : current($this->cmsHelper->config()->getSites()) ?? null;
        $request->attributes->set('_site', "$site");
        $request->attributes->set('_sfs_cms_site', $site);

        $request->attributes->set('_cms_preview', true);

        $compiledData = $this->sectionVersionCompiler->compileRequest($version, $request, false);

        if (!$compiledData->hasErrors()) {
            return new Response($compiledData->getDataPart('content'));
        } else {
            return new Response($compiledData->getDataPart('content') ?? '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
