<?php

namespace Softspring\CmsBundle\Controller\Admin;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidBlockException;
use Softspring\CmsBundle\Form\Admin\Block\BlockCreateFormInterface;
use Softspring\CmsBundle\Form\Admin\Block\BlockListFilterForm;
use Softspring\CmsBundle\Form\Admin\Block\BlockUpdateFormInterface;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Utils\DataMigrator;
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

class BlockController extends AbstractController
{
    use DispatchGetResponseTrait;

    protected FormFactoryInterface $formFactory;
    protected Environment $twig;
    protected BlockManagerInterface $blockManager;
    protected CmsConfig $cmsConfig;
    protected EventDispatcherInterface $eventDispatcher;
    protected array $enabledLocales;

    public function __construct(FormFactoryInterface $formFactory, Environment $twig, BlockManagerInterface $blockManager, CmsConfig $cmsConfig, EventDispatcherInterface $eventDispatcher, array $enabledLocales)
    {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->blockManager = $blockManager;
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

    public function create(string $blockType, Request $request, BlockCreateFormInterface $createForm): Response
    {
        $this->isGranted('PERMISSION_SFS_CMS_ADMIN_BLOCKS_CREATE', $blockType);

        try {
            $config = $this->getBlockConfig($blockType);
        } catch (InvalidBlockException $e) {
            return $this->redirectToRoute('sfs_cms_admin_blocks_list');
        }

        if ($config['singleton'] && $this->blockManager->getRepository()->count(['type' => $blockType]) > 0) {
            $this->addFlash('warning', 'Ya hay una instancia de este bloque.');

            return $this->redirectToRoute('sfs_cms_admin_blocks_list');
        }

        $entity = $this->blockManager->createEntity($blockType);

        $form = $this->createForm(get_class($createForm), $entity, ['block_config' => $config, 'method' => 'POST'])->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->blockManager->saveEntity($entity);

                $this->addFlash('success', 'El bloque se ha creado correctamente. '.(false !== $config['cache_ttl'] ? " Por cuestiones de rendimiento, el bloque está cacheado durante {$config['cache_ttl']} segundos, por lo que puede que tardes en visualizar los cambios." : ''));

                return $this->redirectToRoute('sfs_cms_admin_blocks_list');
            }
        }

        $viewData = new \ArrayObject([
            'block_config' => $config,
            'entity' => $entity,
            'form' => $form->createView(),
        ]);

        return $this->render('@SfsCms/admin/block/create.html.twig', $viewData->getArrayCopy());
    }

    public function update(BlockInterface $block, Request $request, BlockUpdateFormInterface $updateForm): Response
    {
        $this->isGranted('PERMISSION_SFS_CMS_ADMIN_BLOCKS_UPDATE', $block);

        $config = $this->getBlockConfig($block->getType());

        $block->setData(DataMigrator::migrate($config['revision_migration_scripts'], $block->getData(), $config['revision'], $this->cmsConfig));

        $form = $this->createForm(get_class($updateForm), $block, ['block_config' => $config, 'method' => 'POST'])->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->blockManager->saveEntity($block);

                $this->addFlash('success', 'El bloque se ha actualizado. '.(false !== $config['cache_ttl'] ? " Por cuestiones de rendimiento, el bloque está cacheado durante {$config['cache_ttl']} segundos, por lo que puede que tardes en visualizar los cambios." : ''));

                return $this->redirectToRoute('sfs_cms_admin_blocks_list');
            }
        }

        $viewData = new \ArrayObject([
            'block_config' => $config,
            'block' => $block,
            'form' => $form->createView(),
        ]);

        return $this->render('@SfsCms/admin/block/update.html.twig', $viewData->getArrayCopy());
    }

    public function delete(string $block, Request $request): Response
    {
        $this->isGranted('PERMISSION_SFS_CMS_ADMIN_BLOCKS_DELETE', $block);

        //        $config = $this->getBlockConfig($request);
        //
        return new Response();
    }

    public function list(Request $request): Response
    {
        $this->isGranted('PERMISSION_SFS_CMS_ADMIN_BLOCKS_LIST');

        //        if (!empty($config['list_is_granted'])) {
        //            $this->denyAccessUnlessGranted($config['list_is_granted'], null, sprintf('Access denied, user is not %s.', $config['list_is_granted']));
        //        }
        //
        //        if ($response = $this->dispatchGetResponse("sfs_cms.admin.blocks.initialize_event_name", new GetResponseRequestEvent($request))) {
        //            return $response;
        //        }

        $form = $this->createForm(BlockListFilterForm::class)->handleRequest($request);
        $filterEvent = FilterEvent::createFromFilterForm($form, $request);
        $this->dispatch('sfs_cms.admin.blocks.filter_event_name', $filterEvent);
        $entities = $filterEvent->queryPage();

        // show view
        $viewData = new \ArrayObject([
            'entities' => $entities, // @deprecated
            'filterForm' => $form->createView(),
            'config' => $this->cmsConfig->getBlocks(),
        ]);

        $this->dispatch('sfs_cms.admin.blocks.view_event_name', new ViewEvent($viewData));

        if ($request->isXmlHttpRequest()) {
            return $this->render('@SfsCms/admin/block/list-page.html.twig', $viewData->getArrayCopy());
        } else {
            return $this->render('@SfsCms/admin/block/list.html.twig', $viewData->getArrayCopy());
        }
    }

    /**
     * @throws InvalidBlockException
     */
    protected function getBlockConfig(string $blockType): array
    {
        return $this->cmsConfig->getBlock($blockType, true);
    }
}
