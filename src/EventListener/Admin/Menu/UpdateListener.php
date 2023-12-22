<?php

namespace Softspring\CmsBundle\EventListener\Admin\Menu;

use Softspring\CmsBundle\Config\Exception\InvalidMenuException;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\EntityFoundEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UpdateListener extends AbstractMenuListener
{
    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_MENUS_UPDATE_FOUND => 'onFound',
            SfsCmsEvents::ADMIN_MENUS_UPDATE_FORM_PREPARE => 'onFormPrepare',
            SfsCmsEvents::ADMIN_MENUS_UPDATE_SUCCESS => 'onSuccess',
        ];
    }

    public function onFound(EntityFoundEvent $event): void
    {
        $request = $event->getRequest();
        /** @var MenuInterface $entity */
        $entity = $event->getEntity();
        $request->attributes->set('menuType', $entity->getType());

        try {
            // get configuration, and throw exception if not found
            $config = $this->cmsConfig->getMenu($entity->getType(), true);

            // store configuration in request attributes
            $request->attributes->set('menuConfig', $config);
        } catch (InvalidMenuException $e) {
            $this->flashNotifier->addTrans('warning', 'admin_menus.update.invalid_menu_type_flash', ['%$menuType%' => $entity->getType()], 'sfs_cms_admin');
            $event->setResponse(new RedirectResponse($this->router->generate('sfs_cms_admin_menus_list')));
        }
    }

    public function onFormPrepare(FormPrepareEvent $event): void
    {
        $event->setFormOptions([
            'menu_config' => $event->getRequest()->attributes->get('menuConfig'),
            'method' => 'POST',
        ]);
    }

    public function onSuccess(SuccessEvent $event): void
    {
        $config = $event->getRequest()->attributes->get('menuConfig');

        if (false !== $config['cache_ttl']) {
            $this->flashNotifier->addTrans('success', 'admin_menus.update.success_ttl_flash', ['%cacheTtl%' => $config['cache_ttl']], 'sfs_cms_admin');
        } else {
            $this->flashNotifier->addTrans('success', 'admin_menus.update.success_flash', [], 'sfs_cms_admin');
        }
    }
}
