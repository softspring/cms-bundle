<?php

namespace Softspring\CmsBundle\EventListener\Admin\ContentVersion;

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
use Softspring\Component\PolymorphicFormType\Form\Exception\MissingFormTypeException;
use stdClass;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ImportListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_import';

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
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_IMPORT_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_IMPORT_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onCreateEntity', 1],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_IMPORT_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormPrepareResolve', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_IMPORT_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_IMPORT_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_IMPORT_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_IMPORT_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_IMPORT_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailure', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_IMPORT_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_IMPORT_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onView', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_IMPORT_EXCEPTION => [
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
        // prevent default apply
        $event->setApplied(true);

        $request = $event->getRequest();
        $contentConfig = $request->attributes->get('_content_config');
        $entity = $request->attributes->get('content');
        $form = $event->getForm();

        /** @var UploadedFile $zipFile */
        $zipFile = $form->get('file')->getData();

        //        $this->serializer->load('zip', [
        //            'zip_filename' => $zipFile->getPathname(),
        //            'content_to_load_version' => $entity,
        //            'version_origin' => ContentVersionInterface::ORIGIN_IMPORT,
        //            'version_origin_description' => $zipFile->getClientOriginalName(),
        //            'version_keep' => true,
        //            'persist_and_flush' => true,
        //        ]);

        $data = ZipContent::read($zipFile->getPath(), $zipFile->getBasename());

        foreach ($data['contents'] as $id => $content) {
            $contentType = key($content);
            $contentData = current($content);
            $versionData = $contentData['versions'][0];

            if ($contentType !== $contentConfig['_id']) {
                $form->addError(new FormError(sprintf('Incompatible types, you are trying to import a "%s" version to a "%s" content.', $contentType, $contentConfig['_id'])));
                break;
            }

            $version = $this->dataImporter->importVersion($contentType, $entity, $versionData, $data, ['version_origin' => ContentVersionInterface::ORIGIN_IMPORT]);
            $version->setOriginDescription($zipFile->getClientOriginalName());
            $version->setKeep(true);
            $this->contentManager->saveEntity($entity);
        }
    }

    /**
     * @noinspection PhpRouteMissingInspection
     */
    public function onSuccess(SuccessEvent $event): void
    {
        $request = $event->getRequest();
        $contentConfig = $request->attributes->get('_content_config');
        $content = $request->attributes->get('content');

        $this->flashNotifier->addTrans('success', "admin_{$contentConfig['_id']}.version_import.success_flash", [], 'sfs_cms_contents');

        if ($this->getOption($request, 'success_redirect_to')) {
            $url = $this->router->generate($this->getOption($request, 'success_redirect_to'), ['content' => $content]);
        } else {
            $url = $this->router->generate("sfs_cms_admin_content_{$contentConfig['_id']}_versions", ['content' => $content]);
        }

        $event->setResponse(new RedirectResponse($url));
    }

    public function onFailure(FailureEvent $event): void
    {
        $form = $event->getForm();
        $exception = $event->getException();

        $form->addError(new FormError(sprintf('Han error has been produced during importing: %s', $exception->getMessage())));
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
