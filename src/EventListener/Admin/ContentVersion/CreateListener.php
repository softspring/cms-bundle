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
use Softspring\Component\CrudlController\Event\ExceptionEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\FormInvalidEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Softspring\Component\PolymorphicFormType\Form\Exception\MissingFormTypeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Content action.
 */
class CreateListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_create';

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
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onCreateEntity', 1],
                ['onCreateEntityOverrideLayout', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailureShowAlert', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormInvalidShowAlert', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_CREATE_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onCreateEntity(CreateEntityEvent $event): void
    {
        $request = $event->getRequest();

        /** @var ContentInterface $content */
        $content = $request->attributes->get('content');
        $prevVersion = $request->attributes->get('prevVersion');

        if ($prevVersion) {
            $prevVersion = $content->getVersions()->filter(fn (ContentVersionInterface $version) => $version->getId() == $prevVersion)->first();
        }

        $request->attributes->set('prevVersion', $prevVersion ?: $content->getLastVersion());
        $version = $this->contentManager->createVersion($content, $prevVersion, ContentVersionInterface::ORIGIN_EDIT);
        $prevVersion && $version->setOriginDescription('v'.$prevVersion->getVersionNumber());

        $request->attributes->set('version', $version);

        $event->setEntity($version);
    }

    public function onCreateEntityOverrideLayout(CreateEntityEvent $event): void
    {
        $request = $event->getRequest();
        $version = $event->getEntity();

        // override layout from request
        if ($request->request->has('version_create_form')) {
            $version->setLayout($request->request->all()['version_create_form']['layout']);
        }
    }

    public function onFormPrepareResolve(FormPrepareEvent $event): void
    {
        $this->translatableContext->setLocales($event->getEntity()->getContent()->getLocales());

        $version = $event->getEntity();

        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $event->setType($this->getOption($event->getRequest(), 'type'));
        $event->setFormOptions([
            'content' => $event->getRequest()->attributes->get('content'),
            'layout' => $version->getLayout(),
            'method' => 'POST',
            'content_type' => $contentConfig['_id'],
            'content_config' => $contentConfig,
        ]);
    }

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onSuccess(SuccessEvent $event): void
    {
        $request = $event->getRequest();
        $contentConfig = $request->attributes->get('_content_config');
        $version = $event->getEntity();
        $content = $version->getContent();

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.content.success_saved", [], 'sfs_cms_contents');

        switch ($request->request->get('goto')) {
            case 'content':
                $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_content", ['content' => $content]);
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
            $request->attributes->set('_content_version_alert', ['error', 'admin_'.$contentConfig['_id'].'.content.render_error', ['%exception%'=>$exception->getMessage()]]);
        } else if ($exception->getPrevious() instanceof RenderErrorException) {
            $exception->getPrevious()->getRenderErrorList()->formMapErrors($event->getForm());
            $request->attributes->set('_content_version_alert', ['error', 'admin_'.$contentConfig['_id'].'.content.render_error', ['%exception%'=>$exception->getMessage()]]);
        } else if ($exception instanceof CompileException) {
            $request->attributes->set('_content_version_alert', ['error', 'admin_'.$contentConfig['_id'].'.content.render_error', ['%exception%'=>$exception->getMessage()]]);
        }
    }

    public function onFormInvalidShowAlert(FormInvalidEvent $event): void
    {
        $request = $event->getRequest();

        if (1 == $event->getForm()->getErrors()->count() && '_ok' == $event->getForm()->getErrors()[0]->getOrigin()->getName()) {
            return;
        }

        $contentConfig = $request->attributes->get('_content_config');

        $request->attributes->set('_content_version_alert', ['warning', 'admin_'.$contentConfig['_id'].'.content.validation_error']);
    }

    public function onView(ViewEvent $event): void
    {
        parent::onView($event);

        $request = $event->getRequest();
        /** @var ContentInterface $content */
        $content = $request->attributes->get('content');
        /** @var ContentVersionInterface $version */
        $version = $request->attributes->get('version');

        // preview mode
        $request->attributes->set('_cms_preview', true);

        // show alert if exists
        $event->getData()['alert'] = $request->attributes->get('_content_version_alert');

        //        // add enabled locales
        //        $sitesLocales = $content->getSites()->map(fn (SiteInterface $site) => $site->getConfig()['locales'])->toArray();
        //        $enabledLocales = call_user_func_array('array_merge', $sitesLocales);
        //        $enabledLocales = array_unique($enabledLocales);
        /* @deprecated */
        $event->getData()['enabledLocales'] = $content->getLocales();

        // add max_input_vars to prevent errors
        // @see https://www.php.net/manual/en/info.configuration.php#ini.max-input-vars
        $event->getData()['maxInputVars'] = ini_get('max_input_vars');

        // add layout config
        $event->getData()['layout_config'] = $this->cmsConfig->getLayout($version->getLayout());

        // add prev version
        $event->getData()['prev_version'] = $request->attributes->get('prevVersion');
    }

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onException(ExceptionEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        if ($event->getException() instanceof MissingFormTypeException) {
            $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.version_create.module_not_configured_flash", ['%module%' => $event->getException()->getDiscriminator()], 'sfs_cms_contents');

            $url = $this->router->generate("sfs_cms_admin_content_{$event->getRequest()->attributes->get('_content_config')['_id']}_details", ['content' => $event->getRequest()->attributes->get('content')]);
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
