<?php

namespace Softspring\CmsBundle\Controller\Admin;

use Jhg\DoctrinePagination\ORM\PaginatedRepositoryInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Render\ContentRender;
use Softspring\Component\CrudlController\Event\FilterEvent;
use Softspring\Component\Events\DispatchGetResponseTrait;
use Softspring\Component\Events\GetResponseRequestEvent;
use Softspring\Component\Events\ViewEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ContentController extends AbstractController
{
    use DispatchGetResponseTrait;

    protected ContentManagerInterface $contentManager;
    protected RouteManagerInterface $routeManager;
    protected ContentRender $contentRender;
    protected CmsConfig $cmsConfig;
    protected EventDispatcherInterface $eventDispatcher;
    protected array $enabledLocales;

    public function __construct(ContentManagerInterface $contentManager, RouteManagerInterface $routeManager, ContentRender $contentRender, CmsConfig $cmsConfig, EventDispatcherInterface $eventDispatcher, array $enabledLocales)
    {
        $this->contentManager = $contentManager;
        $this->routeManager = $routeManager;
        $this->contentRender = $contentRender;
        $this->cmsConfig = $cmsConfig;
        $this->eventDispatcher = $eventDispatcher;
        $this->enabledLocales = $enabledLocales;
    }

    public function create(Request $request): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

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
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
//
//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['create_view'], $viewData->getArrayCopy());
    }

    public function read(string $content, Request $request): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

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
            'entity' => $entity,
//            'deleteForm' => $deleteForm ? $deleteForm->createView() : null,
        ]);

//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['read_view'], $viewData->getArrayCopy());
    }

    public function update(string $content, Request $request): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

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
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
//
//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['update_view'], $viewData->getArrayCopy());
    }

    public function delete(string $content, Request $request): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

        return new Response();
    }

    public function list(Request $request): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

        if (!empty($config['list_is_granted'])) {
            $this->denyAccessUnlessGranted($config['list_is_granted'], null, sprintf('Access denied, user is not %s.', $config['list_is_granted']));
        }

        if ($response = $this->dispatchGetResponse("sfs_cms.admin.contents.{$config['_id']}.initialize_event_name", new GetResponseRequestEvent($request))) {
            return $response;
        }

        $repo = $this->contentManager->getRepository($config['_id']);

        $listFilterForm = new $config['list_filter_form']();
        $page = $listFilterForm->getPage($request);
        $rpp = $listFilterForm->getRpp($request);
        $orderSort = $listFilterForm->getOrder($request);
        $form = $this->createForm($config['list_filter_form'], [], ['content_config' => $config])->handleRequest($request);
        $filters = $form->isSubmitted() && $form->isValid() ? array_filter($form->getData()) : [];

        $this->dispatch("sfs_cms.admin.contents.{$config['_id']}.filter_event_name", $filterEvent = new FilterEvent($filters, $orderSort, $page, $rpp));
        $filters = $filterEvent->getFilters();
        $orderSort = $filterEvent->getOrderSort();
        $page = $filterEvent->getPage();
        $rpp = $filterEvent->getRpp();

        // get results
        if ($repo instanceof PaginatedRepositoryInterface) {
            $entities = $repo->findPageBy($page, $rpp, $filters, $orderSort);
        } else {
            $entities = $repo->findBy($filters, $orderSort, $rpp, ($page - 1) * $rpp);
        }

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
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
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

        /** @var ContentInterface $entity */
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

        $version = $this->contentManager->createVersion($entity, $prevVersion);

        if ($request->request->get('content_content_form')) {
            $version->setLayout($request->request->get('content_content_form')['layout']);
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

        // show view
        $viewData = new \ArrayObject([
            'content' => $config['_id'],
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
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']] + ['seo' => $config['seo']];

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
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
//
//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['seo_view'], $viewData->getArrayCopy());
    }

    public function preview(string $content, Request $request, ContentVersionInterface $version = null): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

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
            'entity' => $entity,
            'version' => $version ?? $entity->getVersions()->first(),
//            'deleteForm' => $deleteForm ? $deleteForm->createView() : null,
            'enabledLocales' => $this->enabledLocales,
        ]);

//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['preview_view'], $viewData->getArrayCopy());
    }

    public function publishVersion(string $content, Request $request, string $version, ?WebDebugToolbarListener $debugToolbarListener = null): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

        /** @var ContentInterface $entity */
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

    public function previewContent(string $content, Request $request, string $version = null, ?WebDebugToolbarListener $debugToolbarListener = null): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

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

        $debugToolbarListener && $debugToolbarListener->setMode(WebDebugToolbarListener::DISABLED);

        $request->setLocale($request->query->get('locale', 'es'));

        $request->attributes->set('_cms_preview', true);

        return new Response($this->contentRender->render($version ?? $entity->getVersions()->first()));
    }

    public function versions(string $content, Request $request): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

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
            'entity' => $entity,
//            'deleteForm' => $deleteForm ? $deleteForm->createView() : null,
        ]);

//        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData));

        return $this->render($config['versions_view'], $viewData->getArrayCopy());
    }

    public function cleanupVersions(string $content, Request $request): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

        /** @var ContentInterface $entity */
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
                $this->getDoctrine()->getManager()->remove($version);
            }
        }
        $this->contentManager->saveEntity($entity);

        return $this->redirectBack($config['_id'], $entity, $request);
    }

    public function markKeepVersion(string $content, Request $request, string $version, bool $keep): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

        if (!$entity) {
            $request->getSession()->getFlashBag()->add('error', 'entity_not_found');

            return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_list");
        }

        /** @var ContentVersionInterface $version */
        $version = $entity->getVersions()->filter(fn (ContentVersionInterface $versionI) => $versionI->getId() == $version)->first();
        $version->setKeep($keep);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectBack($config['_id'], $entity, $request);
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
