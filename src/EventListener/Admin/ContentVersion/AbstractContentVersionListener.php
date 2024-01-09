<?php

namespace Softspring\CmsBundle\EventListener\Admin\ContentVersion;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\EventListener\Admin\ContentGetOptionTrait;
use Softspring\CmsBundle\EventListener\Admin\ContentInitializeEventTrait;
use Softspring\CmsBundle\EventListener\Admin\ContentRedirectBackTrait;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\Component\CrudlController\Event\LoadEntityEvent;
use Softspring\Component\CrudlController\Event\NotFoundEvent;
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

    protected const ACTION_NAME = '_abstract_';

    public function __construct(
        protected ContentManagerInterface $contentManager,
        protected ContentVersionManagerInterface $contentVersionManager,
        protected RouteManagerInterface $routeManager,
        protected CmsConfig $cmsConfig,
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

        $version = $content->getVersions()->filter(fn (ContentVersionInterface $versionI) => $versionI->getId() === $versionId)->first();
        $event->getRequest()->attributes->set('version', $version);

        $event->setEntity($version);
        $event->setNotFound(!$version);
    }

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onNotFound(NotFoundEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('warning', "admin_{$contentConfig['_id']}.entity_not_found_flash", [], 'sfs_cms_contents');
        $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_list");
        $event->setResponse(new RedirectResponse($url));
    }

    public function onView(ViewEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $event->getData()['content_type'] = $contentConfig['_id'];
        $event->getData()['content_config'] = $contentConfig;
        /* @deprecated */
        $event->getData()['entity'] = $event->getRequest()->attributes->get('content');
        $event->getData()['content_entity'] = $event->getRequest()->attributes->get('content');

        $event->setTemplate($this->getOption($event->getRequest(), 'view'));
    }
}
