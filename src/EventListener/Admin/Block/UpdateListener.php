<?php

namespace Softspring\CmsBundle\EventListener\Admin\Block;

use Softspring\CmsBundle\Config\Exception\InvalidBlockException;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\EntityFoundEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UpdateListener extends AbstractBlockListener
{
    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_BLOCKS_UPDATE_FOUND => 'onFound',
            SfsCmsEvents::ADMIN_BLOCKS_UPDATE_FORM_PREPARE => 'onFormPrepare',
            SfsCmsEvents::ADMIN_BLOCKS_UPDATE_VIEW => 'onViewAddConfig',
            SfsCmsEvents::ADMIN_BLOCKS_UPDATE_SUCCESS => 'onSuccess',
        ];
    }

    public function onFound(EntityFoundEvent $event): void
    {
        $request = $event->getRequest();
        /** @var BlockInterface $entity */
        $entity = $event->getEntity();
        $request->attributes->set('blockType', $entity->getType());

        try {
            // get configuration, and throw exception if not found
            $config = $this->cmsConfig->getBlock($entity->getType(), true);

            // store configuration in request attributes
            $request->attributes->set('blockConfig', $config);
        } catch (InvalidBlockException $e) {
            $this->flashNotifier->addTrans('warning', 'admin_blocks.update.invalid_block_type_flash', ['%$blockType%' => $entity->getType()], 'sfs_cms_admin');
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

    public function onSuccess(SuccessEvent $event): void
    {
        $config = $event->getRequest()->attributes->get('blockConfig');

        if (false !== $config['cache_ttl']) {
            $this->flashNotifier->addTrans('success', 'admin_blocks.update.success_ttl_flash', ['%cacheTtl%' => $config['cache_ttl']], 'sfs_cms_admin');
        } else {
            $this->flashNotifier->addTrans('success', 'admin_blocks.update.success_flash', [], 'sfs_cms_admin');
        }
    }
}
