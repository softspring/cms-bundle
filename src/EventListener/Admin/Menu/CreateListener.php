<?php

namespace Softspring\CmsBundle\EventListener\Admin\Menu;

use Softspring\CmsBundle\Config\Exception\InvalidMenuException;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\CreateEntityEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\InitializeEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreateListener extends AbstractMenuListener
{
    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_MENUS_CREATE_INITIALIZE => [
                ['onInitializeGetConfig', 100],
                ['onInitializeRestrictSingleton', 0],
            ],
            SfsCmsEvents::ADMIN_MENUS_CREATE_FORM_PREPARE => 'onFormPrepare',
            SfsCmsEvents::ADMIN_MENUS_CREATE_ENTITY => 'onCreateEntity',
            SfsCmsEvents::ADMIN_MENUS_CREATE_VIEW => 'onViewAddConfig',
        ];
    }

    public function onInitializeGetConfig(InitializeEvent $event): void
    {
        $request = $event->getRequest();
        $menuType = $request->attributes->get('menuType');

        try {
            // get configuration, and throw exception if not found
            $config = $this->cmsConfig->getMenu($menuType, true);

            // store configuration in request attributes
            $request->attributes->set('menuConfig', $config);
        } catch (InvalidMenuException $e) {
            $this->flashNotifier->addTrans('warning', 'admin_menus.create.invalid_menu_type_flash', ['%$menuType%' => $menuType], 'sfs_cms_admin');
            $event->setResponse(new RedirectResponse($this->router->generate('sfs_cms_admin_menus_list')));
        }
    }

    public function onInitializeRestrictSingleton(InitializeEvent $event): void
    {
        $request = $event->getRequest();
        $menuType = $request->attributes->get('menuType');
        $menuConfig = $request->attributes->get('menuConfig');

        // check if singleton and already exists
        if ($menuConfig['singleton'] && $this->menuManager->getRepository()->count(['type' => $menuType]) > 0) {
            $this->flashNotifier->addTrans('warning', 'admin_menus.create.existing_singleton_instance_flash', ['%$menuType%' => $menuType], 'sfs_cms_admin');
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

    public function onCreateEntity(CreateEntityEvent $event): void
    {
        $request = $event->getRequest();
        $menuType = $request->attributes->get('menuType');

        $entity = $this->menuManager->createEntity($menuType);

        $event->setEntity($entity);
    }
}
