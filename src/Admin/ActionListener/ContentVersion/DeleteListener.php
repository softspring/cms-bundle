<?php

namespace Softspring\CmsBundle\Admin\ActionListener\ContentVersion;

use Softspring\CmsBundle\Helper\CmsHelper;
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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DeleteListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_delete';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsHelper $cmsHelper,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected TranslatableContext $translatableContext,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsHelper, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeUpdateHelperConfig', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 1],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFoundAddFlash', 5],
                ['onNotFoundRedirectToList', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApplySetOtherLastVersion', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_DELETE_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onApplySetOtherLastVersion(ApplyEvent $event): void
    {
        /** @var ContentVersionInterface $deleteVersion */
        $deleteVersion = $event->getEntity();
        $content = $deleteVersion->getContent();

        if ($deleteVersion->isLastVersion()) {
            $previousVersion = $content->getVersions()->filter(function (ContentVersionInterface $version) use ($deleteVersion): bool {
                return $version->getId() !== $deleteVersion->getId();
            })->first();
            $content->setLastVersion($previousVersion);
        }
    }

    public function onFormPrepareResolve(FormPrepareEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $event->setType($contentConfig['admin']['version_delete']['type']);
        $event->setFormOptions([
            'content' => $event->getRequest()->attributes->get('content'),
            'method' => 'POST',
            'content_config' => $contentConfig,
        ]);
    }

    public function onView(ViewEvent $event): void
    {
        parent::onView($event);

        $request = $event->getRequest();
        /** @var ContentInterface $content */
        $content = $request->attributes->get('content');
        /** @var ContentVersionInterface $version */
        $version = $request->attributes->get('version');

        $event->getData()['content_entity'] = $content;
        $event->getData()['version_entity'] = $version;
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.version_delete.success_flash", [], 'sfs_cms_contents');

        $content = $event->getRequest()->attributes->get('content');

        $event->setResponse($this->redirectBack($contentConfig['_id'], $content, $event->getRequest()));
    }
}
