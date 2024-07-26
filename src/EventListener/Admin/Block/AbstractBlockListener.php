<?php

namespace Softspring\CmsBundle\EventListener\Admin\Block;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Request\FlashNotifier;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractBlockListener implements EventSubscriberInterface
{
    public function __construct(
        protected BlockManagerInterface $blockManager,
        protected CmsConfig $cmsConfig,
        protected RouterInterface $router,
        protected FlashNotifier $flashNotifier,
    ) {
    }

    public function onViewAddConfig(ViewEvent $viewEvent): void
    {
        $viewEvent->getData()['block_config'] = $viewEvent->getRequest()->attributes->get('blockConfig');
    }
}
