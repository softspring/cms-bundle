<?php

namespace Softspring\CmsBundle\EventListener\Admin;

use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;
use Softspring\CoreBundle\Event\ViewEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @deprecated
 */
class BlockListener implements EventSubscriberInterface
{
    protected array $blockTypes;

    public function __construct(array $blockTypes)
    {
        $this->blockTypes = $blockTypes;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SfsCmsEvents::ADMIN_BLOCKS_LIST_VIEW => ['onBlockListViewAddBlockTypes'],
            SfsCmsEvents::ADMIN_BLOCKS_CREATE_INITIALIZE => ['onBlockCreateInitializeForm'],
        ];
    }

    public function onBlockListViewAddBlockTypes(ViewEvent $event): void
    {
        $event->getData()['blockTypes'] = $this->blockTypes;
    }

    public function onBlockCreateInitializeForm(GetResponseEntityEvent $event)
    {
        /** @var BlockInterface $entity */
        $entity = $event->getEntity();
        $entity->setKey($event->getRequest()->attributes->get('type'));
    }
}
