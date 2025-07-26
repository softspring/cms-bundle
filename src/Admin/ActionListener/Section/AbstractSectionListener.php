<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\Admin\ActionListener\ExceptionMessageTrait;
use Softspring\CmsBundle\Admin\ActionListener\SectionRedirectBackTrait;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\NotFoundEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class AbstractSectionListener implements EventSubscriberInterface
{
    use SectionRedirectBackTrait;
    use ExceptionMessageTrait;

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

    public function onNotFoundAddFlashAndRedirectToList(NotFoundEvent $event): void
    {
        $this->flashNotifier->addTrans('warning', 'admin_sections.entity_not_found_flash', [], 'sfs_cms_admin');
        $url = $this->router->generate('sfs_cms_admin_sections_list');
        $event->setResponse(new RedirectResponse($url));
    }

    public function onSuccessAddFlash(SuccessEvent $event): void
    {
        $this->flashNotifier->addTrans('success', 'admin_sections.'.static::ACTION_NAME.'.success_flash', [], 'sfs_cms_admin');
    }

    public function onSuccessRedirectBack(SuccessEvent $event): void
    {
        /** @var SectionInterface $entity */
        $entity = $event->getEntity();
        $event->setResponse($this->redirectBack($entity, $event->getRequest()));
    }

    public function onFailureAddFormError(FailureEvent $event): void
    {
        $event->getForm()->addError(new FormError($this->extractExceptionMessage($event->getException())));
    }

    public function onFailureAddFlash(FailureEvent $event): void
    {
        $this->flashNotifier->addTrans('error', 'admin_sections.'.static::ACTION_NAME.'.failed_flash', ['%exception%' => $event->getException()->getMessage()], 'sfs_cms_admin');
    }

    public function onFailureRedirectBack(FailureEvent $event): void
    {
        /** @var SectionInterface $entity */
        $entity = $event->getEntity();
        $event->setResponse($this->redirectBack($entity, $event->getRequest()));
    }

    public function onViewAddSectionEntity(ViewEvent $event): void
    {
        $event->getData()['section_entity'] = $event->getRequest()->attributes->get('section');
    }
}
