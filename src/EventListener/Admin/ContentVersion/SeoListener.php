<?php

namespace Softspring\CmsBundle\EventListener\Admin\ContentVersion;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Render\CompileException;
use Softspring\CmsBundle\Render\RenderErrorException;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\Component\CrudlController\Event\CreateEntityEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SeoListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_seo';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected TranslatableContext $translatableContext
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSeoLoadEntity', 1],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailureShowAlert', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_SEO_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onSeoLoadEntity(CreateEntityEvent $event): void
    {
        $request = $event->getRequest();

        /** @var ContentInterface $content */
        $content = $request->attributes->get('content');
        $prevVersion = $request->query->get('version');

        if ($prevVersion) {
            $prevVersion = $content->getVersions()->filter(fn (ContentVersionInterface $version) => $version->getId() == $prevVersion)->first();
        }

        $request->attributes->set('prevVersion', $prevVersion ?: $content->getLastVersion());
        $version = $this->contentManager->createVersion($content, $prevVersion, ContentVersionInterface::ORIGIN_SEO);
        $prevVersion && $version->setOriginDescription('v'.$prevVersion->getVersionNumber());

        $request->attributes->set('version', $version);

        $event->setEntity($version);
    }

    public function onFormPrepareResolve(FormPrepareEvent $event): void
    {
        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();

        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->translatableContext->setLocales($version->getContent()->getLocales());

        $event->setType($this->getOption($event->getRequest(), 'type'));
        $event->setFormOptions([
            'content' => $event->getRequest()->attributes->get('content'),
            'method' => 'POST',
            'content_type' => $contentConfig['_id'],
            'content_config' => $contentConfig,
        ]);

        // set data for form
        $event->setData($version);
    }

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onSuccess(SuccessEvent $event): void
    {
        $request = $event->getRequest();
        $contentConfig = $request->attributes->get('_content_config');
        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();
        $content = $version->getContent();

        if ($version->hasCompileErrors()) {
            $this->flashNotifier->addTrans('warning', "admin_{$contentConfig['_id']}.version_seo.success_saved_with_compile_errors", [], 'sfs_cms_contents');
        } else {
            $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.version_seo.success_saved", [], 'sfs_cms_contents');
        }

        switch ($request->request->get('goto')) {
            case 'content':
                $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_content", ['content' => $content, 'saved' => 1]);
                $event->setResponse(new RedirectResponse($url));
                break;

            case 'preview':
                $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_preview", ['content' => $content]);
                $event->setResponse(new RedirectResponse($url));
                break;

            case 'publish':
                $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_publish_version", ['content' => $content, 'version' => $version]);
                $event->setResponse(new RedirectResponse($url));
                break;

            default:
                if ($redirectTo = $this->getOption($request, 'success_redirect_to')) {
                    $event->setResponse(new RedirectResponse($this->router->generate($redirectTo, ['content' => $content])));
                } else {
                    $event->setResponse($this->redirectBack($contentConfig['_id'], $content, $request));
                }
        }
    }

    public function onFailureShowAlert(FailureEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getException();
        $contentConfig = $request->attributes->get('_content_config');

        if ($exception instanceof RenderErrorException) {
            $exception->getRenderErrorList()->formMapErrors($event->getForm());
            $request->attributes->set('_content_version_alert', ['error', 'admin_'.$contentConfig['_id'].'.version_seo.render_error', ['%exception%' => $exception->getMessage()]]);
        } elseif ($exception->getPrevious() instanceof RenderErrorException) {
            $exception->getPrevious()->getRenderErrorList()->formMapErrors($event->getForm());
            $request->attributes->set('_content_version_alert', ['error', 'admin_'.$contentConfig['_id'].'.version_seo.render_error', ['%exception%' => $exception->getMessage()]]);
        } elseif ($exception instanceof CompileException) {
            $request->attributes->set('_content_version_alert', ['error', 'admin_'.$contentConfig['_id'].'.version_seo.render_error', ['%exception%' => $exception->getMessage()]]);
        }
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

        // add prev version
        $event->getData()['prev_version'] = $request->attributes->get('prevVersion');

        // show alert if exists
        $event->getData()['alert'] = $request->attributes->get('_content_version_alert');
    }
}
