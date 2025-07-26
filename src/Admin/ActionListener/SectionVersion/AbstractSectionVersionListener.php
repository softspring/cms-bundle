<?php

namespace Softspring\CmsBundle\Admin\ActionListener\SectionVersion;

use Softspring\CmsBundle\Admin\ActionListener\ExceptionMessageTrait;
use Softspring\CmsBundle\Admin\ActionListener\SectionRedirectBackTrait;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\Component\CrudlController\Event\ExceptionEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\NotFoundEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
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
        protected CmsHelper $cmsHelper,
        protected RouterInterface $router,
        protected FlashNotifier $flashNotifier,
        protected AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function onLoadSectionEntity(Event $event): void
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

    public function onNotFoundAddFlashAndRedirectToList(NotFoundEvent $event): void
    {
        $this->flashNotifier->addTrans('warning', 'admin_sections.version_entity_not_found_flash', [], 'sfs_cms_admin');
        $url = $this->router->generate('sfs_cms_admin_sections_versions', ['section' => $event->getRequest()->attributes->get('section')]);
        $event->setResponse(new RedirectResponse($url));
    }

    public function onViewAddEntities(ViewEvent $event): void
    {
        $event->getData()['section_entity'] = $event->getRequest()->attributes->get('section');
        $event->getData()['version_entity'] = $event->getRequest()->attributes->get('version');
    }

    public function onSuccessAddFlash(SuccessEvent $event): void
    {
        $this->flashNotifier->addTrans('success', 'admin_sections.'.static::ACTION_NAME.'.success_flash', [], 'sfs_cms_admin');
    }

    public function onSuccessRedirectBack(SuccessEvent $event): void
    {
        /** @var SectionVersionInterface $version */
        $version = $event->getEntity();
        $event->setResponse($this->redirectBack($version->getSection(), $event->getRequest(), $version));
    }

    public function onFailureAddFlash(FailureEvent $event): void
    {
        $this->flashNotifier->addTrans('error', 'admin_sections.'.static::ACTION_NAME.'.failed_flash', [
            '%exception%' => $event->getException()->getMessage(),
        ], 'sfs_cms_admin');
    }

    public function onFailureRedirectBack(FailureEvent $event): void
    {
        /** @var SectionVersionInterface $version */
        $version = $event->getEntity();
        $event->setResponse($this->redirectBack($version->getSection(), $event->getRequest(), $version));
    }

    public function onExceptionAddFlash(ExceptionEvent $event): void
    {
        $this->flashNotifier->addTrans('error', 'admin_sections.'.static::ACTION_NAME.'.failed_flash', [
            '%exception%' => $event->getException()->getMessage(),
        ], 'sfs_cms_admin');
    }

    public function onExceptionRedirectBack(ExceptionEvent $event): void
    {
        /** @var SectionVersionInterface $version */
        $version = $event->getRequest()->attributes->get('version');
        $event->setResponse($this->redirectBack($version->getSection(), $event->getRequest(), $version));
    }
}
