<?php

namespace Softspring\CmsBundle\EventListener\Admin\Menu;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ViewEvent;

class ListListener extends AbstractMenuListener
{
    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_MENUS_LIST_VIEW => 'onView',
        ];
    }

    public function onView(ViewEvent $event): void
    {
        $event->getData()['menus_config'] = $this->cmsConfig->getMenus();
    }
}
