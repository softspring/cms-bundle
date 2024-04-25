<?php

namespace Softspring\CmsBundle\EventListener\Admin\Content;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Data\DataImporter;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CmsBundle\Utils\ZipContent;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\CreateEntityEvent;
use Softspring\Component\CrudlController\Event\ExceptionEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Softspring\Component\PolymorphicFormType\Form\Exception\MissingFormTypeException;
use stdClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ImportListener extends AbstractContentListener
{
    protected const ACTION_NAME = 'import';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected DataImporter $dataImporter,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENTS_IMPORT_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_IMPORT_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onCreateEntity', 1],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_IMPORT_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_IMPORT_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_IMPORT_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_IMPORT_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_IMPORT_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_IMPORT_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailure', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_IMPORT_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_IMPORT_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENTS_IMPORT_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ];
    }

    public function onCreateEntity(CreateEntityEvent $event): void
    {
        // set dummy version
        $version = new stdClass();
        $version->file = null;
        $event->setEntity($version);
    }

    public function onFormPrepareResolve(FormPrepareEvent $event): void
    {
        $event->setType($this->getOption($event->getRequest(), 'type'));
        $event->setFormOptions([
            'method' => 'POST',
            'content_config' => $event->getRequest()->attributes->get('_content_config'),
        ]);
    }

    public function onApply(ApplyEvent $event): void
    {
        /** @var UploadedFile $zipFile */
        $zipFile = $event->getForm()->get('file')->getData();

        $this->dataImporter->import(ZipContent::read($zipFile->getPath(), $zipFile->getBasename()), ['version_origin' => ContentVersionInterface::ORIGIN_IMPORT]);

        //        $this->serializer->load('zip', [
        //            'zip_filename' => $zipFile->getPathname(),
        //            'version_origin' => ContentVersionInterface::ORIGIN_IMPORT,
        //            'persist_and_flush' => true,
        //        ]);

        $event->setApplied(true);
    }

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onSuccess(SuccessEvent $event): void
    {
        //        $contentConfig = $event->getRequest()->attributes->get('_content_config');
        //
        //        if (empty($contentConfig['admin']['import']['success_redirect_to'])) {
        //            $contentConfig['admin']['import']['success_redirect_to'] = "sfs_cms_admin_content_{$contentConfig['_id']}_content";
        //        }
        //
        //        $redirectUrl = $this->router->generate($contentConfig['admin']['import']['success_redirect_to'], ['content' => $event->getEntity()]);
        //
        //        $event->setResponse(new RedirectResponse($redirectUrl));
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.import.success_flash", [], 'sfs_cms_contents');

        $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_list");

        $event->setResponse(new RedirectResponse($url));
    }

    public function onFailure(FailureEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        if ($event->getException() instanceof UniqueConstraintViolationException) {
            $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.import.duplicated_flash", ['%exception%' => ''], 'sfs_cms_contents');
        } else {
            $exception = $event->getException()->getMessage();
            if ('1' === $event->getRequest()->server->get('APP_DEBUG')) {
                $exception .= '<br/><br/>'.get_class($event->getException());
                $exception .= '<br/><br/>'.nl2br($event->getException()->getTraceAsString());
            }
            $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.import.failure_flash", ['%exception%' => $exception], 'sfs_cms_contents');
        }

        $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_list");

        $event->setResponse(new RedirectResponse($url));
    }

    public function onView(ViewEvent $event): void
    {
        parent::onView($event);

        //        $request = $event->getRequest();
        //        /** @var ContentInterface $content */
        //        $content = $request->attributes->get('content');
        //        /** @var ContentVersionInterface $version */
        //        $version = $request->attributes->get('version');
        //
        //        // preview mode
        //        $request->attributes->set('_cms_preview', true);
        //
        //        // add enabled locales
        //        $sitesLocales = $content->getSites()->map(fn(SiteInterface $site) => $site->getConfig()['locales'])->toArray();
        //        $enabledLocales = call_user_func_array('array_merge', $sitesLocales);
        //        $enabledLocales = array_unique($enabledLocales);
        //        $event->getData()['enabledLocales'] = $enabledLocales;
        //
        //        // add max_input_vars to prevent errors
        //        // @see https://www.php.net/manual/en/info.configuration.php#ini.max-input-vars
        //        $event->getData()['maxInputVars'] = ini_get('max_input_vars');
        //
        //        // add layout config
        //        $event->getData()['layout_config'] = $this->cmsConfig->getLayout($version->getLayout());
    }

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onException(ExceptionEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        if ($event->getException() instanceof MissingFormTypeException) {
            $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.version_import.module_not_configured_flash", ['%module%' => $event->getException()->getDiscriminator()], 'sfs_cms_contents');

            $url = $this->router->generate("sfs_cms_admin_content_{$event->getRequest()->attributes->get('_content_config')['_id']}_details", ['content' => $event->getRequest()->attributes->get('content')]);
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
