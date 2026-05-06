<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Admin\ActionListener\Block;

use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\ViewEvent;

class ListListener extends AbstractBlockListener
{
    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_BLOCKS_LIST_VIEW => 'onView',
        ];
    }

    public function onView(ViewEvent $event): void
    {
        $event->getData()['blocks_config'] = $this->cmsConfig->getBlocks();
    }
}
