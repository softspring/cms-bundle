<?php

namespace Softspring\CmsBundle\Controller\Admin;

use Jhg\DoctrinePagination\ORM\PaginatedRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidMenuException;
use Softspring\CmsBundle\Form\Admin\Menu\MenuForm;
use Softspring\CmsBundle\Form\Admin\Menu\MenuListFilterForm;
use Softspring\CmsBundle\Manager\MenuManagerInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\Component\CrudlController\Event\FilterEvent;
use Softspring\CoreBundle\Controller\Traits\DispatchGetResponseTrait;
use Softspring\CoreBundle\Event\ViewEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MenuController extends AbstractController
{
    use DispatchGetResponseTrait;

    protected MenuManagerInterface $menuManager;
    protected CmsConfig $cmsConfig;
    protected EventDispatcherInterface $eventDispatcher;
    protected array $enabledLocales;

    public function __construct(MenuManagerInterface $menuManager, CmsConfig $cmsConfig, EventDispatcherInterface $eventDispatcher, array $enabledLocales)
    {
        $this->menuManager = $menuManager;
        $this->cmsConfig = $cmsConfig;
        $this->eventDispatcher = $eventDispatcher;
        $this->enabledLocales = $enabledLocales;
    }

    /**
     * @Security(expression="is_granted('ROLE_SFS_CMS_ADMIN_MENUS_CREATE', menuType)")
     */
    public function create(string $menuType, Request $request): Response
    {
        try {
            $config = $this->getMenuConfig($menuType);
        } catch (InvalidMenuException $e) {
            return $this->redirectToRoute('sfs_cms_admin_menus_list');
        }

        if ($config['singleton'] && $this->menuManager->getRepository()->count(['type' => $menuType]) > 0) {
            $this->addFlash('warning', 'Ya hay una instancia de este menú.');

            return $this->redirectToRoute('sfs_cms_admin_menus_list');
        }

        $entity = $this->menuManager->createEntity($menuType);

        $form = $this->createForm(MenuForm::class, $entity, ['menu_config' => $config, 'method' => 'POST'])->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->menuManager->saveEntity($entity);

                $this->addFlash('success', 'El menú se ha creado correctamente. '.(false !== $config['cache_ttl'] ? " Por cuestiones de rendimiento, el menú está cacheado durante {$config['cache_ttl']} segundos, por lo que puede que tardes en visualizar los cambios." : ''));

                return $this->redirectToRoute('sfs_cms_admin_menus_list');
            }
        }

        $viewData = new \ArrayObject([
            'entity' => $entity,
            'form' => $form->createView(),
        ]);

        return $this->render('@SfsCms/admin/menu/create.html.twig', $viewData->getArrayCopy());
    }

    /**
     * @Security(expression="is_granted('ROLE_SFS_CMS_ADMIN_MENUS_UPDATE', menu)")
     */
    public function update(MenuInterface $menu, Request $request): Response
    {
        $config = $this->getMenuConfig($menu->getType());

        $form = $this->createForm(MenuForm::class, $menu, ['menu_config' => $config, 'method' => 'POST'])->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->menuManager->saveEntity($menu);

                $this->addFlash('success', 'El menú se ha actualizado. '.(false !== $config['cache_ttl'] ? " Por cuestiones de rendimiento, el menú está cacheado durante {$config['cache_ttl']} segundos, por lo que puede que tardes en visualizar los cambios." : ''));

                return $this->redirectToRoute('sfs_cms_admin_menus_list');
            }
        }

        $viewData = new \ArrayObject([
            'menu' => $menu,
            'form' => $form->createView(),
        ]);

        return $this->render('@SfsCms/admin/menu/update.html.twig', $viewData->getArrayCopy());
    }

    public function delete(string $menu, Request $request): Response
    {
//        $config = $this->getMenuConfig($request);
//
//        return new Response();
    }

    public function list(Request $request): Response
    {
//        if (!empty($config['list_is_granted'])) {
//            $this->denyAccessUnlessGranted($config['list_is_granted'], null, sprintf('Access denied, user is not %s.', $config['list_is_granted']));
//        }
//
//        if ($response = $this->dispatchGetResponse("sfs_cms.admin.menus.initialize_event_name", new GetResponseRequestEvent($request))) {
//            return $response;
//        }

        $repo = $this->menuManager->getRepository();

        $listFilterForm = new MenuListFilterForm();
        $page = $listFilterForm->getPage($request);
        $rpp = $listFilterForm->getRpp($request);
        $orderSort = $listFilterForm->getOrder($request);
        $form = $this->createForm(MenuListFilterForm::class)->handleRequest($request);
        $filters = $form->isSubmitted() && $form->isValid() ? array_filter($form->getData()) : [];

        $this->dispatch('sfs_cms.admin.menus.filter_event_name', $filterEvent = new FilterEvent($filters, $orderSort, $page, $rpp));
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
            'entities' => $entities, // @deprecated
            'filterForm' => $form->createView(),
            'menus_config' => $this->cmsConfig->getMenus(),
        ]);

        $this->dispatch('sfs_cms.admin.menus.view_event_name', new ViewEvent($viewData));

        if ($request->isXmlHttpRequest()) {
            return $this->render('@SfsCms/admin/menu/list-page.html.twig', $viewData->getArrayCopy());
        } else {
            return $this->render('@SfsCms/admin/menu/list.html.twig', $viewData->getArrayCopy());
        }
    }

    /**
     * @throws InvalidMenuException
     */
    protected function getMenuConfig(string $menuType): array
    {
        return $this->cmsConfig->getMenu($menuType, true);
    }
}
