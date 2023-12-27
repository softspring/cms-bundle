<?php

namespace Softspring\CmsBundle\EventListener\Admin\ContentVersion;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Data\DataExporter;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CmsBundle\Utils\Slugger;
use Softspring\CmsBundle\Utils\ZipContent;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\ExceptionEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ExportListener extends AbstractContentVersionListener
{
    protected const ACTION_NAME = 'version_export';

    public function __construct(
        ContentManagerInterface $contentManager,
        ContentVersionManagerInterface $contentVersionManager,
        RouteManagerInterface $routeManager,
        CmsConfig $cmsConfig,
        RouterInterface $router,
        FlashNotifier $flashNotifier,
        AuthorizationCheckerInterface $authorizationChecker,
        protected DataExporter $dataExporter,
    ) {
        parent::__construct($contentManager, $contentVersionManager, $routeManager, $cmsConfig, $router, $flashNotifier, $authorizationChecker);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_EXPORT_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeIsGranted', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_EXPORT_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_EXPORT_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFound', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_EXPORT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_EXPORT_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_EXPORT_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_EXPORT_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailureOrException', 0],
            ],
            SfsCmsEvents::ADMIN_CONTENT_VERSIONS_EXPORT_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailureOrException', 0],
            ],
        ];
    }

    public function onApply(ApplyEvent $event): void
    {
        $event->setApplied(true);

        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        /** @var ContentVersionInterface $version */
        $version = $event->getEntity();
        /** @var ContentInterface $content */
        $content = $event->getRequest()->attributes->get('content');

        //        $exportName = sprintf('%s-%s-v%s-%s.zip', Slugger::lowerSlug($content->getName()), $contentConfig['_id'], $version->getVersionNumber(), date('Y-m-d-H-i-s'));
        //        $this->serializer->dump(['contents' => [$content]], 'zip', [
        //            'zip_filename' => $exportName,
        //            'encoder' => 'yaml',
        //            'dump_version' => ContentEntityNormalizer::DUMP_VERSION_ID,
        //            'version_id' => $version->getId(),
        //        ]);
        //
        //        $event->setResponse(ZipContent::response($exportName));

        $path = tempnam(sys_get_temp_dir(), 'content_');
        unlink($path);
        $this->dataExporter->exportContent($content, $version, $contentConfig, $path);
        $exportName = sprintf('%s/%s-%s-v%s-%s.zip', sys_get_temp_dir(), Slugger::lowerSlug($content->getName()), $contentConfig['_id'], $version->getVersionNumber(), date('Y-m-d-H-i-s'));

        $event->setEntity(ZipContent::dumpResponse($path, $exportName));
    }

    public function onSuccess(SuccessEvent $event): void
    {
        if ($event->getEntity() instanceof Response) {
            $event->setResponse($event->getEntity());
        }
    }

    public function onFailureOrException(ExceptionEvent|FailureEvent $event): void
    {
        $contentConfig = $event->getRequest()->attributes->get('_content_config');

        $this->flashNotifier->addTrans('error', "admin_{$contentConfig['_id']}.version_export.failed_flash", ['%exception%' => $event->getException()->getMessage()], 'sfs_cms_contents');

        $content = $event->getRequest()->attributes->get('content');

        $event->setResponse($this->redirectBack($contentConfig['_id'], $content, $event->getRequest()));
    }
}
