<?php

namespace Softspring\CmsBundle\Admin\ActionListener\ContentVersion;

use Softspring\CmsBundle\Admin\ActionListener\ContentGetOptionTrait;
use Softspring\CmsBundle\Admin\ActionListener\ContentInitializeEventTrait;
use Softspring\CmsBundle\Admin\ActionListener\ContentRedirectBackTrait;
use Softspring\CmsBundle\Admin\ActionListener\ExceptionMessageTrait;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\EntityEvent;
use Softspring\Component\CrudlController\Event\ExceptionEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\LoadEntityEvent;
use Softspring\Component\CrudlController\Event\NotFoundEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractContentVersionListener implements EventSubscriberInterface
{
    use ContentGetOptionTrait;
    use ContentInitializeEventTrait;
    use ContentRedirectBackTrait;
    use ExceptionMessageTrait;

    protected const ACTION_NAME = '_abstract_';

    public function __construct(
        protected ContentManagerInterface $contentManager,
        protected ContentVersionManagerInterface $contentVersionManager,
        protected RouteManagerInterface $routeManager,
        protected CmsHelper $cmsHelper,
        protected RouterInterface $router,
        protected FlashNotifier $flashNotifier,
        protected AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function onEventDispatchContentTypeEvent(object $event, string $eventName, EventDispatcherInterface $dispatcher): void
    {
        $eventName = str_replace('sfs_cms.admin.content_versions.', '', $eventName);

        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $dispatcher->dispatch($event, sprintf('sfs_cms.admin.content_versions.%s.%s', $contentConfig['_id'], $eventName));
    }

    public function onEventLoadContentEntity(Event $event): void
    {
        if (!method_exists($event, 'getRequest')) {
            return;
        }

        $contentId = $event->getRequest()->attributes->get('content');
        $contentConfig = $event->getRequest()->attributes->get('_content_config');
        $entity = $this->contentManager->getRepository($contentConfig['_id'])->findOneBy(['id' => $contentId]);

        if (!$entity instanceof ContentInterface) {
            if (method_exists($event, 'setResponse')) {
                $this->flashNotifier->addTrans('warning', "admin_{$contentConfig['_id']}.entity_not_found_flash", [], 'sfs_cms_contents');
                $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_list");
                $event->setResponse(new RedirectResponse($url));

                return;
            }

            throw new NotFoundHttpException();
        }

        $event->getRequest()->attributes->set('content', $entity);
    }

    public function onLoadEntity(LoadEntityEvent $event): void
    {
        $versionId = $event->getRequest()->attributes->get('version');

        /** @var ContentInterface $content */
        $content = $event->getRequest()->attributes->get('content');

        $version = $this->contentVersionManager->getRepository()->findOneBy(['id' => $versionId, 'content' => $content]);
        $event->getRequest()->attributes->set('version', $version);

        $event->setEntity($version);
        $event->setNotFound(!$version);
    }

    public function onNotFoundAddFlash(NotFoundEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');
        $this->flashNotifier->addTrans('warning', "admin_{$contentConfig['_id']}.entity_not_found_flash", [], 'sfs_cms_contents');
    }

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onNotFoundRedirectToList(NotFoundEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');
        $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_list");
        $event->setResponse(new RedirectResponse($url));
    }

    public function onEventSaveVersion(EntityEvent $event): void
    {
        $this->contentVersionManager->saveEntity($event->getEntity());
    }

    public function onEventSaveContent(EntityEvent $event): void
    {
        $this->contentManager->saveEntity($event->getEntity()->getContent());
    }

    public function onViewAddEntities(ViewEvent $event): void
    {
        $event->getData()['content_entity'] = $event->getRequest()->attributes->get('content');
    }

    public function onView(ViewEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $event->getData()['content_type'] = $contentConfig['_id'];
        $event->getData()['content_config'] = $contentConfig;

        $event->setTemplate($this->getOption($event->getRequest(), 'view'));
    }

    public function onApplySetApplied(ApplyEvent $event): void
    {
        $event->setApplied(true);
    }

    public function onSuccessAddFlash(SuccessEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');
        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.".static::ACTION_NAME.'.success_flash', [], 'sfs_cms_contents');
    }

    public function onFailureAddFlash(FailureEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.".static::ACTION_NAME.'.failed_flash', [
            '%exception%' => $event->getException()->getMessage(),
            // '%exception_details%' => $event->getException() instanceof CompileExceptionDetailsInterface ? $event->getException()->getDetails() : '',
        ], 'sfs_cms_contents');
    }

    public function onExceptionAddFlash(ExceptionEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.".static::ACTION_NAME.'.failed_flash', [
            '%exception%' => $event->getException()->getMessage(),
            // '%exception_details%' => $event->getException() instanceof CompileExceptionDetailsInterface ? $event->getException()->getDetails() : '',
        ], 'sfs_cms_contents');
    }

    public function onEventRedirectBack(EntityEvent|ExceptionEvent $event): void
    {
        if ($event instanceof EntityEvent) {
            /** @var ContentVersionInterface $version */
            $version = $event->getEntity();
        } else {
            $version = $event->getRequest()->attributes->get('version');
        }

        if (!$version instanceof ContentVersionInterface) {
            $version = null;
        }

        $contentConfig = $event->getRequest()->attributes->get('_content_config');
        $content = $event->getRequest()->attributes->get('content');
        $event->setResponse($this->redirectBack($contentConfig['_id'], $content, $event->getRequest(), $version));
    }
}
