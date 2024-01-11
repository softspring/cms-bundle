<?php

namespace Softspring\CmsBundle\EventListener\Admin\ContentVersion;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Render\RenderErrorException;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CmsBundle\Translator\ExtractException;
use Softspring\CmsBundle\Translator\InvalidTranslationMappingException;
use Softspring\CmsBundle\Translator\TranslatableContext;
use Softspring\CmsBundle\Translator\TranslationsTransformer;
use Softspring\CmsBundle\Translator\TranslatorExtractor;
use Softspring\Component\CrudlController\Event\ApplyEvent;
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

class TranslationsListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_translations';

    public function __construct(ContentManagerInterface $contentManager, ContentVersionManagerInterface $contentVersionManager, RouteManagerInterface $routeManager, CmsConfig $cmsConfig, RouterInterface $router, FlashNotifier $flashNotifier, AuthorizationCheckerInterface $authorizationChecker, protected TranslatorExtractor $translatorExtractor, protected TranslatableContext $translatableContext)
    {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_TRANSLATIONS_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_TRANSLATIONS_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onTranslationsEntity', 1],
                ['onTranslationsEntityOverrideLayout', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_TRANSLATIONS_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_TRANSLATIONS_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_TRANSLATIONS_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_TRANSLATIONS_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_TRANSLATIONS_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_TRANSLATIONS_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailureShowAlert', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_TRANSLATIONS_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormInvalidShowAlert', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_TRANSLATIONS_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_TRANSLATIONS_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onException', 0],
            ],
        ];
    }

    public function onTranslationsEntity(CreateEntityEvent $event): void
    {
        $request = $event->getRequest();

        /** @var ContentInterface $content */
        $content = $request->attributes->get('content');
        $prevVersion = $request->query->get('version');

        if ($prevVersion) {
            $prevVersion = $content->getVersions()->filter(fn (ContentVersionInterface $version) => $version->getId() == $prevVersion)->first();
        }

        $request->attributes->set('prevVersion', $prevVersion ?: $content->getLastVersion());
        $version = $this->contentManager->createVersion($content, $prevVersion, ContentVersionInterface::ORIGIN_TRANSLATIONS);
        $prevVersion && $version->setOriginDescription('v'.$prevVersion->getVersionNumber());

        $request->attributes->set('version', $version);

        $event->setEntity($version);
    }

    public function onTranslationsEntityOverrideLayout(CreateEntityEvent $event): void
    {
        $request = $event->getRequest();
        $version = $event->getEntity();

        // override layout from request
        if ($request->request->has('version_translations_form')) {
            $version->setLayout($request->request->all()['version_translations_form']['layout']);
        }
    }

    /**
     * @throws ExtractException
     */
    public function onFormPrepareResolve(FormPrepareEvent $event): void
    {
        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();

        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->translatableContext->setLocales($version->getContent()->getLocales());
        $flattenTranslations = TranslationsTransformer::flatten($this->translatorExtractor->extract($version));

        $event->setType($this->getOption($event->getRequest(), 'type'));
        $event->setFormOptions([
            'content' => $event->getRequest()->attributes->get('content'),
            'method' => 'POST',
            'content_type' => $contentConfig['_id'],
            'content_config' => $contentConfig,
            'flatten_translations' => $flattenTranslations,
        ]);

        // set data for form
        $event->setData($flattenTranslations);
    }

    /**
     * @throws InvalidTranslationMappingException
     */
    public function onApply(ApplyEvent $event): void
    {
        $version = $event->getEntity();
        $flattenTranslations = $event->getForm()->getData();

        $version->setData(TranslationsTransformer::applyFlatten($version->getData(), $flattenTranslations));

        $event->setApplied(false); // do save entity
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

            $request->attributes->set('_content_version_alert', ['error', 'admin_'.$contentConfig['_id'].'.content.render_error']);
        }
    }

    public function onFormInvalidShowAlert(FormInvalidEvent $event): void
    {
        $request = $event->getRequest();
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

        $event->getData()['content_entity'] = $content;
        $event->getData()['version_entity'] = $version;

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
            $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.version_translations.module_not_configured_flash", ['%module%' => $event->getException()->getDiscriminator()], 'sfs_cms_contents');

            $url = $this->router->generate("sfs_cms_admin_content_{$event->getRequest()->attributes->get('_content_config')['_id']}_details", ['content' => $event->getRequest()->attributes->get('content')]);
            $event->setResponse(new RedirectResponse($url));

            return;
        }

        if ($event->getException() instanceof InvalidTranslationMappingException) {
            // TODO manage this

            return;
        }

        if ($event->getException() instanceof ExtractException) {
            // TODO manage this

            return;
        }
    }
}
