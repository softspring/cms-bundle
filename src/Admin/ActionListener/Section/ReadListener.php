<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\NotFoundEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ReadListener extends AbstractSectionListener
{
    protected const ACTION_NAME = 'read';

    public function __construct(
        SectionManagerInterface $sectionManager,
        SectionVersionManagerInterface $sectionVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected ContentVersionManagerInterface $contentVersionManager,
        //        protected string $sectionCacheType,
    ) {
        parent::__construct($sectionManager, $sectionVersionManager, $routeManager, $cmsConfig, $cmsHelper, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // SfsCmsEvents::ADMIN_SECTIONS_READ_INITIALIZE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_READ_LOAD_ENTITY => [],
            SfsCmsEvents::ADMIN_SECTIONS_READ_NOT_FOUND => [
                ['onNotFound', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_READ_FOUND => [],
            SfsCmsEvents::ADMIN_SECTIONS_READ_VIEW => [
                ['onView', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_READ_EXCEPTION => [],
        ];
    }

    public function onNotFound(NotFoundEvent $event): void
    {
        $this->flashNotifier->addTrans('warning', 'admin_sections.entity_not_found_flash', [], 'sfs_cms_admin');
        $url = $this->router->generate('sfs_cms_admin_sections_list');
        $event->setResponse(new RedirectResponse($url));
    }

    public function onView(ViewEvent $event): void
    {
        parent::onView($event);

        /** @var SectionInterface $section */
        $section = $event->getData()['section'];

        $event->getData()['lastestVersions'] = $this->sectionVersionManager->getLatestVersions($section, 3);
        // $event->getData()['sectionCacheLastModifiedEnabled'] = 'last_modified' === $this->sectionCacheType;

        $event->getData()['linked_cms_versions'] = array_merge(
            $this->contentVersionManager->getRepository()->createQueryBuilder('cv')
                ->leftJoin('cv.content', 'c')
                ->where(':section MEMBER OF cv.sections')
                ->setParameter('section', $section)
                ->getQuery()
                ->getResult(),
            $this->sectionVersionManager->getRepository()->createQueryBuilder('sv')
                ->leftJoin('sv.section', 's')
                ->where(':section MEMBER OF sv.sections')
                ->setParameter('section', $section)
                ->getQuery()
                ->getResult()
        );
    }
}
