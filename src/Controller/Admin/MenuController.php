<?php

namespace Softspring\CmsBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidMenuException;
use Softspring\CmsBundle\Form\Admin\Menu\MenuForm;
use Softspring\CmsBundle\Form\Admin\Menu\MenuListFilterFormInterface;
use Softspring\CmsBundle\Manager\MenuManagerInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\Component\CrudlController\Event\FilterEvent;
use Softspring\Component\Events\DispatchGetResponseTrait;
use Softspring\Component\Events\ViewEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class MenuController extends AbstractController
{
    use DispatchGetResponseTrait;

    protected FormFactoryInterface $formFactory;
    protected Environment $twig;
    protected MenuManagerInterface $menuManager;
    protected CmsConfig $cmsConfig;
    protected EventDispatcherInterface $eventDispatcher;
    protected array $enabledLocales;

    public function __construct(FormFactoryInterface $formFactory, Environment $twig, MenuManagerInterface $menuManager, CmsConfig $cmsConfig, EventDispatcherInterface $eventDispatcher, array $enabledLocales)
    {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->menuManager = $menuManager;
        $this->cmsConfig = $cmsConfig;
        $this->eventDispatcher = $eventDispatcher;
        $this->enabledLocales = $enabledLocales;
    }

    protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface
    {
        return $this->formFactory->create($type, $data, $options);
    }

    protected function renderView(string $view, array $parameters = []): string
    {
        return $this->twig->render($view, $parameters);
    }

    /**
     * @Security(expression="is_granted('PERMISSION_SFS_CMS_ADMIN_MENUS_CREATE', menuType)")
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
     * @Security(expression="is_granted('PERMISSION_SFS_CMS_ADMIN_MENUS_UPDATE', menu)")
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

    /**
     * @Security(expression="is_granted('PERMISSION_SFS_CMS_ADMIN_MENUS_DELETE', menu)")
     */
    public function delete(string $menu, Request $request): Response
    {
        //        $config = $this->getMenuConfig($request);

        return new Response();
    }

    /**
     * @Security(expression="is_granted('PERMISSION_SFS_CMS_ADMIN_MENUS_LIST')")
     */
    public function list(Request $request, MenuListFilterFormInterface $filterForm): Response
    {
        //        if (!empty($config['list_is_granted'])) {
        //            $this->denyAccessUnlessGranted($config['list_is_granted'], null, sprintf('Access denied, user is not %s.', $config['list_is_granted']));
        //        }
        //
        //        if ($response = $this->dispatchGetResponse("sfs_cms.admin.menus.initialize_event_name", new GetResponseRequestEvent($request))) {
        //            return $response;
        //        }

        $form = $this->createForm(get_class($filterForm))->handleRequest($request);
        $filterEvent = FilterEvent::createFromFilterForm($form, $request);
        $this->dispatch('sfs_cms.admin.menus.filter_event_name', $filterEvent);
        $entities = $filterEvent->queryPage();

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
