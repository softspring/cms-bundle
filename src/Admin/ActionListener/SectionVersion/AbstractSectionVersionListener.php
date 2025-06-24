<?php

namespace Softspring\CmsBundle\Admin\ActionListener\SectionVersion;

use Softspring\CmsBundle\Admin\ActionListener\ExceptionMessageTrait;
use Softspring\CmsBundle\Admin\ActionListener\SectionRedirectBackTrait;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\Component\CrudlController\Event\LoadEntityEvent;
use Softspring\Component\CrudlController\Event\NotFoundEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractSectionVersionListener implements EventSubscriberInterface
{
    use ExceptionMessageTrait;
    use SectionRedirectBackTrait;

    protected const ACTION_NAME = '_abstract_';

    public function __construct(
        protected SectionManagerInterface $sectionManager,
        protected SectionVersionManagerInterface $sectionVersionManager,
        protected RouteManagerInterface $routeManager,
        protected CmsConfig $cmsConfig,
        protected RouterInterface $router,
        protected FlashNotifier $flashNotifier,
        protected AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function onEventLoadSectionEntity(Event $event): void
    {
        if (!method_exists($event, 'getRequest')) {
            return;
        }

        $sectionId = $event->getRequest()->attributes->get('section');
        $entity = $this->sectionManager->getRepository()->findOneBy(['id' => $sectionId]);

        if (!$entity instanceof SectionInterface) {
            if (method_exists($event, 'setResponse')) {
                $this->flashNotifier->addTrans('warning', 'admin_sections.entity_not_found_flash', [], 'sfs_cms_admin');
                $url = $this->router->generate('sfs_cms_admin_sections_list');
                $event->setResponse(new RedirectResponse($url));

                return;
            }

            throw new NotFoundHttpException();
        }

        $event->getRequest()->attributes->set('section', $entity);
    }

    //    public function onLoadEntity(LoadEntityEvent $event): void
    //    {
    //        $versionId = $event->getRequest()->attributes->get('version');
    //
    //        /** @var SectionInterface $section */
    //        $section = $event->getRequest()->attributes->get('section');
    //
    //        $version = $section->getVersions()->filter(fn (SectionVersionInterface $versionI) => $versionI->getId() === $versionId)->first();
    //        $event->getRequest()->attributes->set('version', $version);
    //
    //        $event->setEntity($version);
    //        $event->setNotFound(!$version);
    //    }
    //
    //    /**
    //     * @noinspection PhpRouteMissingInspection
    //     */
    //    public function onNotFound(NotFoundEvent $event): void
    //    {
    //        $sectionConfig = $event->getRequest()->attributes->get('_section_config');
    //
    //        $this->flashNotifier->addTrans('warning', "admin_{$sectionConfig['_id']}.entity_not_found_flash", [], 'sfs_cms_admin');
    //        $url = $this->router->generate("sfs_cms_admin_section_{$sectionConfig['_id']}_list");
    //        $event->setResponse(new RedirectResponse($url));
    //    }

    public function onView(ViewEvent $event): void
    {
        $event->getData()['section_entity'] = $event->getRequest()->attributes->get('section');
        $event->getData()['version_entity'] = $event->getRequest()->attributes->get('version');
    }
}
