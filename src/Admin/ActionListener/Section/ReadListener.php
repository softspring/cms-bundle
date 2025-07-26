<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ReadListener extends AbstractSectionListener
{
    protected const ACTION_NAME = 'read';

    public function __construct(
        SectionManagerInterface $sectionManager,
        SectionVersionManagerInterface $sectionVersionManager,
        RouteManagerInterface $routeManager,
        CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected ContentVersionManagerInterface $contentVersionManager,
        //        protected string $sectionCacheType,
    ) {
        parent::__construct($sectionManager, $sectionVersionManager, $routeManager, $cmsHelper, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // SfsCmsEvents::ADMIN_SECTIONS_READ_INITIALIZE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_READ_LOAD_ENTITY => [],
            SfsCmsEvents::ADMIN_SECTIONS_READ_NOT_FOUND => [
                ['onNotFoundAddFlashAndRedirectToList', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_READ_FOUND => [],
            SfsCmsEvents::ADMIN_SECTIONS_READ_VIEW => [
                ['onViewAddSectionEntity', 10],
                ['onViewAddVersionsInfo', 0],
            ],
            // SfsCmsEvents::ADMIN_SECTIONS_READ_EXCEPTION => [],
        ];
    }

    public function onViewAddVersionsInfo(ViewEvent $event): void
    {
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
