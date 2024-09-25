<?php

namespace Softspring\CmsBundle\EventListener\Admin\Block;

use Softspring\CmsBundle\Config\Exception\InvalidBlockException;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\CreateEntityEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\InitializeEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreateListener extends AbstractBlockListener
{
    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_BLOCKS_CREATE_INITIALIZE => [
                ['onInitializeGetConfig', 100],
                ['onInitializeRestrictSingleton', 0],
            ],
            SfsCmsEvents::ADMIN_BLOCKS_CREATE_FORM_PREPARE => 'onFormPrepare',
            SfsCmsEvents::ADMIN_BLOCKS_CREATE_ENTITY => 'onCreateEntity',
            SfsCmsEvents::ADMIN_BLOCKS_CREATE_VIEW => 'onViewAddConfig',
        ];
    }

    public function onInitializeGetConfig(InitializeEvent $event): void
    {
        $request = $event->getRequest();
        $blockType = $request->attributes->get('blockType');

        try {
            // get configuration, and throw exception if not found
            $config = $this->cmsConfig->getBlock($blockType, true);

            // store configuration in request attributes
            $request->attributes->set('blockConfig', $config);
        } catch (InvalidBlockException $e) {
            $this->flashNotifier->addTrans('warning', 'admin_blocks.create.invalid_block_type_flash', ['%$blockType%' => $blockType], 'sfs_cms_admin');
            $event->setResponse(new RedirectResponse($this->router->generate('sfs_cms_admin_blocks_list')));
        }
    }

    public function onInitializeRestrictSingleton(InitializeEvent $event): void
    {
        $request = $event->getRequest();
        $blockType = $request->attributes->get('blockType');
        $blockConfig = $request->attributes->get('blockConfig');

        // check if singleton and already exists
        if ($blockConfig['singleton'] && $this->blockManager->getRepository()->count(['type' => $blockType]) > 0) {
            $this->flashNotifier->addTrans('warning', 'admin_blocks.create.existing_singleton_instance_flash', ['%$blockType%' => $blockType], 'sfs_cms_admin');
            $event->setResponse(new RedirectResponse($this->router->generate('sfs_cms_admin_blocks_list')));
        }
    }

    public function onFormPrepare(FormPrepareEvent $event): void
    {
        $event->setFormOptions([
            'block_config' => $event->getRequest()->attributes->get('blockConfig'),
            'method' => 'POST',
        ]);
    }

    public function onCreateEntity(CreateEntityEvent $event): void
    {
        $request = $event->getRequest();
        $blockType = $request->attributes->get('blockType');

        $entity = $this->blockManager->createEntity($blockType);

        $event->setEntity($entity);
    }
}
