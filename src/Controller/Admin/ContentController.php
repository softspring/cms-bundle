<?php

namespace Softspring\CmsBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Data\DataExporter;
use Softspring\CmsBundle\Data\DataImporter;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Render\ContentRender;
use Softspring\CmsBundle\Utils\Slugger;
use Softspring\CmsBundle\Utils\ZipContent;
use Softspring\Component\CrudlController\Event\FilterEvent;
use Softspring\Component\Events\DispatchGetResponseTrait;
use Softspring\Component\Events\GetResponseRequestEvent;
use Softspring\Component\Events\ViewEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ContentController extends AbstractController
{
    use DispatchGetResponseTrait;

    protected EntityManagerInterface $em;

    protected ContentManagerInterface $contentManager;
    protected RouteManagerInterface $routeManager;
    protected ContentRender $contentRender;
    protected CmsConfig $cmsConfig;
    protected EventDispatcherInterface $eventDispatcher;
    protected array $enabledLocales;
    protected DataImporter $dataImporter;
    protected DataExporter $dataExporter;
    protected ?WebDebugToolbarListener $webDebugToolbarListener;

    public function __construct(EntityManagerInterface $em, ContentManagerInterface $contentManager, RouteManagerInterface $routeManager, ContentRender $contentRender, CmsConfig $cmsConfig, EventDispatcherInterface $eventDispatcher, array $enabledLocales, DataImporter $dataImporter, DataExporter $dataExporter, ?WebDebugToolbarListener $webDebugToolbarListener)
    {
        $this->em = $em;
        $this->contentManager = $contentManager;
        $this->routeManager = $routeManager;
        $this->contentRender = $contentRender;
        $this->cmsConfig = $cmsConfig;
        $this->eventDispatcher = $eventDispatcher;
        $this->enabledLocales = $enabledLocales;
        $this->dataImporter = $dataImporter;
        $this->dataExporter = $dataExporter;
        $this->webDebugToolbarListener = $webDebugToolbarListener;
    }

    public function create(Request $request): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], null, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

        $entity = $this->contentManager->createEntity($config['_id']);
        $entity->addRoute($this->routeManager->createEntity());

//        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
//            return $response;
//        }

        $form = $this->createForm($config['create_type'], $entity, ['content' => $config, 'method' => 'POST'])->handleRequest($request);
//
//        $this->dispatchFromConfig($config, 'form_init_event_name', new FormEvent($form, $request));
//
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_valid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }

                $entity->getRoutes()->map(function (RouteInterface $route) use ($entity) {
                    $route->setSite($entity->getSite());
                });
                $this->contentManager->saveEntity($entity);

//                if ($response = $this->dispatchGetResponseFromConfig($config, 'success_event_name', new GetResponseEntityEvent($entity, $request))) {
//                    return $response;
//                }

                return $this->redirect(!empty($config['create_success_redirect_to']) ? $this->generateUrl($config['create_success_redirect_to']) : $this->generateUrl("sfs_cms_admin_content_{$config['_id']}_content", ['content' => $entity]));
//            } else {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }
            }
        }

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
            'content_config' => $contentConfig,
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
//
//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['create_view'], $viewData->getArrayCopy());
    }

    public function read(string $content, Request $request): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

//        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
//            return $response;
//        }

//        $deleteForm = $this->getDeleteForm($entity, $request, $this->deleteForm);

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
            'content_config' => $contentConfig,
            'entity' => $entity,
//            'deleteForm' => $deleteForm ? $deleteForm->createView() : null,
        ]);

//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['read_view'], $viewData->getArrayCopy());
    }

    public function update(string $content, Request $request): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], null, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

//        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
//            return $response;
//        }

        $form = $this->createForm($config['update_type'], $entity, ['content' => $config, 'method' => 'POST'])->handleRequest($request);
//
//        $this->dispatchFromConfig($config, 'form_init_event_name', new FormEvent($form, $request));
//
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_valid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }

                $this->contentManager->saveEntity($entity);

//                if ($response = $this->dispatchGetResponseFromConfig($config, 'success_event_name', new GetResponseEntityEvent($entity, $request))) {
//                    return $response;
//                }

                return !empty($config['update_success_redirect_to']) ? $this->redirectToRoute($config['update_success_redirect_to']) : $this->redirectBack($config['_id'], $entity, $request);
//            } else {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }
            }
        }

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
            'content_config' => $contentConfig,
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
//
//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['update_view'], $viewData->getArrayCopy());
    }

    public function delete(string $content, Request $request): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

        /** @var ContentInterface|null $entity */
        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], null, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

//        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
//            return $response;
//        }

        $form = $this->createForm($config['delete_type'], $entity, ['content' => $config, 'method' => 'POST', 'entity' => $entity])->handleRequest($request);
//
//        $this->dispatchFromConfig($config, 'form_init_event_name', new FormEvent($form, $request));
//
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_valid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }

                switch ($form->get('action')->getData()) {
                    case 'change':
                        foreach ($entity->getRoutes() as $route) {
                            $route->setContent($form->get('content')->getData());
                        }
                        break;

                    case 'redirect':
                        foreach ($entity->getRoutes() as $route) {
                            $route->setContent(null);
                            $route->setType(RouteInterface::TYPE_REDIRECT_TO_ROUTE);
                            $route->setRedirectType(Response::HTTP_MOVED_PERMANENTLY); // 301
                            $route->setSymfonyRoute($form->get('symfonyRoute')->getData());
                        }
                        break;

                    case 'delete':
                    default:
                        // do nothing, route will be deleted with cascade
                }

                $this->contentManager->deleteEntity($entity);

//                if ($response = $this->dispatchGetResponseFromConfig($config, 'success_event_name', new GetResponseEntityEvent($entity, $request))) {
//                    return $response;
//                }

                return !empty($config['delete_success_redirect_to']) ? $this->redirectToRoute($config['delete_success_redirect_to']) : $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
//            } else {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }
            }
        }

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
            'content_config' => $contentConfig,
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
//
//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['delete_view'], $viewData->getArrayCopy());
    }

    public function import(Request $request, bool $confirm = false): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], null, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

//        $entity = $this->contentManager->createEntity($config['_id']);
//        $entity->addRoute($this->routeManager->createEntity());

//        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
//            return $response;
//        }

        $form = $this->createForm($config['import_type'], null, ['content' => $config, 'method' => 'POST'])->handleRequest($request);
//
//        $this->dispatchFromConfig($config, 'form_init_event_name', new FormEvent($form, $request));
//
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_valid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }

                /** @var UploadedFile $zipFile */
                $zipFile = $form->getData()['file'];
                $this->dataImporter->import(ZipContent::read($zipFile->getPath(), $zipFile->getBasename()), ['version_origin' => ContentVersionInterface::ORIGIN_IMPORT]);

//                if ($response = $this->dispatchGetResponseFromConfig($config, 'success_event_name', new GetResponseEntityEvent($entity, $request))) {
//                    return $response;
//                }

                return $this->redirect(!empty($config['import_success_redirect_to']) ? $this->generateUrl($config['import_success_redirect_to']) : $this->generateUrl("sfs_cms_admin_content_{$config['_id']}_list"));
//            } else {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }
            }
        }

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
            'content_config' => $contentConfig,
            'form' => $form->createView(),
            'confirm' => $confirm,
        ]);
//
//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['import_view'], $viewData->getArrayCopy());
    }

    public function list(Request $request): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

        if (!empty($config['list_is_granted'])) {
            $this->denyAccessUnlessGranted($config['list_is_granted'], null, sprintf('Access denied, user is not %s.', $config['list_is_granted']));
        }

        if ($response = $this->dispatchGetResponse("sfs_cms.admin.contents.{$config['_id']}.initialize_event_name", new GetResponseRequestEvent($request))) {
            return $response;
        }

        $form = $this->createForm($config['list_filter_form'], [], ['content_config' => $contentConfig, 'class' => $this->contentManager->getTypeClass($config['_id'])])->handleRequest($request);
        $filterEvent = FilterEvent::createFromFilterForm($form, $request);
        $this->dispatch("sfs_cms.admin.contents.{$config['_id']}.filter_event_name", $filterEvent);
        $entities = $filterEvent->queryPage();

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
            'content_config' => $contentConfig,
            'entities' => $entities, // @deprecated
            'filterForm' => $form->createView(),
            'read_route' => $config['read_route'] ?? null,
            'list_page_view' => $config['list_page_view'],
        ]);

        $this->dispatch("sfs_cms.admin.contents.{$config['_id']}.view_event_name", new ViewEvent($viewData));

        if ($request->isXmlHttpRequest()) {
            return $this->render($config['list_page_view'], $viewData->getArrayCopy());
        } else {
            return $this->render($config['list_view'], $viewData->getArrayCopy());
        }
    }

    public function content(string $content, Request $request, string $prevVersion = null): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

        /** @var ?ContentInterface $entity */
        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], null, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

        $request->attributes->set('content', $entity);

//        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
//            return $response;
//        }

        if ($prevVersion) {
            $prevVersion = $entity->getVersions()->filter(fn (ContentVersionInterface $version) => $version->getId() == $prevVersion)->first();
        }

        $version = $this->contentManager->createVersion($entity, $prevVersion, ContentVersionInterface::ORIGIN_EDIT);

        /** @var ?array $contentContentForm */
        $contentContentForm = $request->request->get('content_content_form');
        if ($contentContentForm) {
            $version->setLayout($contentContentForm['layout']);
        }

        $form = $this->createForm($config['content_type'], $version, [
            'layout' => $version->getLayout(),
            'method' => 'POST',
            'content_type' => $config['_id'],
        ])->handleRequest($request);
//
//        $this->dispatchFromConfig($config, 'form_init_event_name', new FormEvent($form, $request));
//
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_valid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }

                $this->contentManager->saveEntity($entity);

//                if ($response = $this->dispatchGetResponseFromConfig($config, 'success_event_name', new GetResponseEntityEvent($entity, $request))) {
//                    return $response;
//                }

                if ('content' == $request->request->get('goto')) {
                    return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_content", ['content' => $entity]);
                }

                if ('preview' == $request->request->get('goto')) {
                    return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_preview", ['content' => $entity]);
                }

                if ('publish' == $request->request->get('goto')) {
                    return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_publish_version", ['content' => $entity, 'version' => $version]);
                }

                return !empty($config['content_success_redirect_to']) ? $this->redirectToRoute($config['content_success_redirect_to']) : $this->redirectBack($config['_id'], $entity, $request);
//            } else {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }
            }
        }

        $request->attributes->set('_cms_preview', true);

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
            'content_config' => $contentConfig,
            'entity' => $entity,
            'layout' => $this->cmsConfig->getLayout($version->getLayout()),
            'form' => $form->createView(),
            'enabledLocales' => $this->enabledLocales,
        ]);
//
//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['content_view'], $viewData->getArrayCopy());
    }

    public function seo(string $content, Request $request): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']] + ['seo' => $contentConfig['seo']];

        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], null, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

//        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
//            return $response;
//        }

        $form = $this->createForm($config['seo_type'], $entity, ['content' => $config, 'method' => 'POST'])->handleRequest($request);
//
//        $this->dispatchFromConfig($config, 'form_init_event_name', new FormEvent($form, $request));
//
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_valid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }

                $this->contentManager->saveEntity($entity);

//                if ($response = $this->dispatchGetResponseFromConfig($config, 'success_event_name', new GetResponseEntityEvent($entity, $request))) {
//                    return $response;
//                }

                return !empty($config['seo_success_redirect_to']) ? $this->redirectToRoute($config['seo_success_redirect_to']) : $this->redirectBack($config['_id'], $entity, $request);
//            } else {
//                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
//                    return $response;
//                }
            }
        }

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
            'content_config' => $contentConfig,
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
//
//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['seo_view'], $viewData->getArrayCopy());
    }

    public function preview(string $content, Request $request, ContentVersionInterface $version = null): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

//        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
//            return $response;
//        }

//        $deleteForm = $this->getDeleteForm($entity, $request, $this->deleteForm);

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
            'content_config' => $contentConfig,
            'entity' => $entity,
            'version' => $version ?? $entity->getVersions()->first(),
//            'deleteForm' => $deleteForm ? $deleteForm->createView() : null,
            'enabledLocales' => $this->enabledLocales,
        ]);

//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['preview_view'], $viewData->getArrayCopy());
    }

    public function publishVersion(string $content, Request $request, string $version): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

        /** @var ?ContentInterface $entity */
        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

        if ($version) {
            $version = $entity->getVersions()->filter(fn (ContentVersionInterface $versionI) => $versionI->getId() == $version)->first();
        }

        if ($version) {
            // TODO call to compile methods (usefull if republishing)
            $entity->setPublishedVersion($version);
            $this->contentManager->saveEntity($entity);
        }

        $this->addFlash('success', 'version_has_been_published');

        return $this->redirectBack($config['_id'], $entity, $request, $version);
    }

    public function previewContent(string $content, Request $request, string $version = null): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

        if ($version) {
            $version = $entity->getVersions()->filter(fn (ContentVersionInterface $versionI) => $versionI->getId() == $version)->first();
        }

        $this->webDebugToolbarListener && $this->webDebugToolbarListener->setMode(WebDebugToolbarListener::DISABLED);

        $request->setLocale($request->query->get('locale', 'es'));

        $request->attributes->set('_cms_preview', true);

        return new Response($this->contentRender->render($version ?? $entity->getVersions()->first()));
    }

    public function versions(string $content, Request $request): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

//        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
//            return $response;
//        }

//        $deleteForm = $this->getDeleteForm($entity, $request, $this->deleteForm);

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
            'content_config' => $contentConfig,
            'entity' => $entity,
//            'deleteForm' => $deleteForm ? $deleteForm->createView() : null,
        ]);

//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['versions_view'], $viewData->getArrayCopy());
    }

    public function cleanupVersions(string $content, Request $request): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

        /** @var ?ContentInterface $entity */
        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

        /** @var ContentVersionInterface $version */
        foreach ($entity->getVersions() as $version) {
            if ($version->deleteOnCleanup()) {
                $entity->removeVersion($version); // TODO THIS SHOULD REMOVE VERSIONS
                $this->em->remove($version);
            }
        }
        $this->contentManager->saveEntity($entity);

        return $this->redirectBack($config['_id'], $entity, $request);
    }

    public function markKeepVersion(string $content, Request $request, string $version, bool $keep): Response
    {
        $contentConfig = $this->getContentConfig($request);
        $config = $contentConfig['admin'] + ['_id' => $contentConfig['_id']];

        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

        /** @var ContentVersionInterface $version */
        $version = $entity->getVersions()->filter(fn (ContentVersionInterface $versionI) => $versionI->getId() == $version)->first();
        $version->setKeep($keep);
        $this->em->flush();

        return $this->redirectBack($config['_id'], $entity, $request);
    }

    public function exportVersion(string $content, Request $request, string $version): Response
    {
        $config = $this->getContentConfig($request);

        /** @var ?ContentInterface $content */
        $content = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

        if (!$content) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

        /** @var ContentVersionInterface $version */
        $version = $content->getVersions()->filter(fn (ContentVersionInterface $versionI) => $versionI->getId() == $version)->first();

        $path = tempnam(sys_get_temp_dir(), 'content_');
        unlink($path);
        $this->dataExporter->exportContent($content, $version, $config, $path);
        $exportName = sprintf('%s/%s-%s-v%s-%s.zip', sys_get_temp_dir(), Slugger::lowerSlug($content->getName()), $config['_id'], $version->getVersionNumber(), date('Y-m-d-H-i-s'));

        return ZipContent::dumpResponse($path, $exportName);
    }

    protected function redirectBack(string $configId, ContentInterface $entity, Request $request, ?ContentVersionInterface $version = null): RedirectResponse
    {
        switch ($request->query->get('back')) {
            case 'versions':
                return $this->redirectToRoute("sfs_cms_admin_content_{$configId}_versions", ['content' => $entity]);

            case 'preview':
                if ($version) {
                    return $this->redirectToRoute("sfs_cms_admin_content_{$configId}_preview", ['content' => $entity]);
                } else {
                    return $this->redirectToRoute("sfs_cms_admin_content_{$configId}_preview_version", ['content' => $entity, 'version' => $version]);
                }

                // no break
            default:
                return $this->redirectToRoute("sfs_cms_admin_content_{$configId}_details", ['content' => $entity]);
        }
    }

    /**
     * @throws \Softspring\CmsBundle\Config\Exception\InvalidContentException
     */
    protected function getContentConfig(Request $request): array
    {
        if (!$request->attributes->has('_content_type')) {
            throw new \Exception('_content_type is required in route defaults');
        }

        return $this->cmsConfig->getContent($request->attributes->get('_content_type'), true);
    }
}
