<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

use Softspring\CmsBundle\Admin\ActionListener\ExceptionMessageTrait;
use Softspring\CmsBundle\Admin\ActionListener\SectionRedirectBackTrait;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Manager\SectionVersionManagerInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\LoadEntityEvent;
use Softspring\Component\CrudlController\Event\NotFoundEvent;
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
        protected CmsConfig $cmsConfig,
        protected CmsHelper $cmsHelper,
        protected RouterInterface $router,
        protected FlashNotifier $flashNotifier,
        protected AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    //    public function onLoadEntity(LoadEntityEvent $event): void
    //    {
    //        $sectionId = $event->getRequest()->attributes->get('section');
    //        $entity = $this->sectionManager->getRepository()->findOneBy(['id' => $sectionId]);
    //        $event->setEntity($entity);
    //        $event->setNotFound(!$entity);
    //        $event->getRequest()->attributes->set('section', $entity);
    //    }

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onNotFound(NotFoundEvent $event): void
    {
        $this->flashNotifier->addTrans('warning', 'admin_sections.entity_not_found_flash', [], 'sfs_cms_admin');
        $url = $this->router->generate('sfs_cms_admin__sections_list');
        $event->setResponse(new RedirectResponse($url));
    }

    public function onFailureAddFormError(FailureEvent $event): void
    {
        $event->getForm()->addError(new FormError($event->getException()->getMessage()));
    }

    public function onView(ViewEvent $event): void
    {
        $event->getData()['section_entity'] = $event->getRequest()->attributes->get('section');
    }
}
