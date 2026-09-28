<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Admin\ActionListener\Menu;

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
