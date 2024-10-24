<?php

namespace Softspring\CmsBundle\EventListener\Admin\Content;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UpdateListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'update';

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
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFilterFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_UPDATE_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onFilterFormPrepareResolve(FormPrepareEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->translatableContext->setLocales($event->getEntity()->getLocales());

        $event->setType($contentConfig['admin']['update']['type']);
        $event->setFormOptions([
            'method' => 'POST',
            'content_config' => $contentConfig,
            'content' => $event->getEntity(),
        ]);
    }

    public function onApply(ApplyEvent $event): void
    {
        if (!$event->getForm()->has('addLocale')) {
            return;
        }

        /** @var ContentInterface $content */
        $content = $event->getEntity();
        $addLocales = $event->getForm()->get('addLocale')->getData();
        if (!empty($addLocales)) {
            foreach ($addLocales as $locale) {
                $content->addLocale($locale);
            }

            $lastVersion = $content->getLastVersion();
            $newVersion = $this->contentManager->createVersion($content, $lastVersion, ContentVersionInterface::ORIGIN_ADD_LOCALE);
            $newVersion->setOriginDescription('v'.$lastVersion->getVersionNumber().' + '.implode(',', $addLocales));

            foreach ($addLocales as $locale) {
                $this->contentVersionManager->addLocale($newVersion, $locale);
            }
        }
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.update.success_flash", [], 'sfs_cms_contents');

        if (empty($contentConfig['admin']['update']['success_redirect_to'])) {
            /** @var ContentInterface $entity */
            $entity = $event->getEntity();
            $event->setResponse($this->redirectBack($contentConfig['_id'], $entity, $event->getRequest()));
        } else {
            $redirectUrl = $this->router->generate($contentConfig['admin']['update']['success_redirect_to'], ['content' => $event->getEntity()]);

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }

    public function onView(ViewEvent $event): void
    {
        $event->getData()['entity'] = $event->getData()['content'];
        parent::onView($event);
    }
}
