<?php

namespace Softspring\CmsBundle\EventListener\Admin\Content;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\EntityFoundEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DuplicateListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'duplicate';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected TranslatableContext $translatableContext,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFoundCreateDuplicate', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFilterFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 8],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailureAddFormError', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_DUPLICATE_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onFoundCreateDuplicate(EntityFoundEvent $event): void
    {
        /** @var ContentInterface $originalEntity */
        $originalEntity = $event->getEntity();
        $newEntity = $this->contentManager->duplicateEntity($originalEntity);
        $event->setEntity($newEntity);
    }

    public function onFilterFormPrepareResolve(FormPrepareEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->translatableContext->setLocales($event->getEntity()->getLocales());

        $event->setType($contentConfig['admin']['duplicate']['type']);
        $event->setFormOptions([
            'method' => 'POST',
            'content_config' => $contentConfig,
            'origin_content' => $event->getRequest()->attributes->get('content'),
        ]);
    }

    public function onApply(ApplyEvent $event): void
    {
        /** @var ContentInterface $originContent */
        $originContent = $event->getRequest()->attributes->get('content');

        /** @var ContentVersionInterface $versionToBeCopied */
        $versionToBeCopied = $event->getForm()->get('duplicateVersion')->getData();
        /** @var ContentInterface $newContent */
        $newContent = $event->getEntity();

        $originDescription = $originContent->getName().' (v'.$versionToBeCopied->getVersionNumber().')';
        $newContent->addVersion($newVersion = $this->contentVersionManager->duplicateEntity($versionToBeCopied, $newContent, $originDescription));
        $newVersion->setVersionNumber(0);
        $newContent->setLastVersionNumber(0);
        $newContent->setLastVersion($newVersion);

        $newContent->getRoutes()->map(function (RouteInterface $route) use ($newContent) {
            foreach ($newContent->getSites() as $site) {
                $route->addSite($site);
            }
        });
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.duplicate.success_flash", [], 'sfs_cms_contents');

        if (empty($contentConfig['admin']['duplicate']['success_redirect_to'])) {
            /** @var ContentInterface $entity */
            $entity = $event->getEntity();
            $event->setResponse($this->redirectBack($contentConfig['_id'], $entity, $event->getRequest()));
        } else {
            $redirectUrl = $this->router->generate($contentConfig['admin']['duplicate']['success_redirect_to'], ['content' => $event->getEntity()]);

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }

    public function onView(ViewEvent $event): void
    {
        $event->getData()['origin_entity'] = $event->getRequest()->attributes->get('content');
        $event->getData()['entity'] = $event->getData()['content'];
        parent::onView($event);
    }
}
