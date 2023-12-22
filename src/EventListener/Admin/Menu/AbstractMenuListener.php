<?php

namespace Softspring\CmsBundle\EventListener\Admin\Menu;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\MenuManagerInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractMenuListener implements EventSubscriberInterface
{
    public function __construct(
        protected MenuManagerInterface $menuManager,
        protected CmsConfig $cmsConfig,
        protected RouterInterface $router,
        protected FlashNotifier $flashNotifier,
    ) {
    }
}
