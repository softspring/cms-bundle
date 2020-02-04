<?php

namespace Softspring\CmsBundle\EventListener\Admin;

use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\SfsCmsEvents;
use Softspring\CoreBundle\Event\ViewEvent;
use Softspring\CrudlBundle\Event\GetResponseEntityEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BlockListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    protected $blockTypes;

    /**
     * BlockListener constructor.
     *
     * @param array $blockTypes
     */
    public function __construct(array $blockTypes)
    {
        $this->blockTypes = $blockTypes;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SfsCmsEvents::ADMIN_BLOCKS_LIST_VIEW => ['onBlockListViewAddBlockTypes'],
            SfsCmsEvents::ADMIN_BLOCKS_CREATE_INITIALIZE => ['onBlockCreateInitializeForm'],
        ];
    }

    /**
     * @param ViewEvent $event
     */
    public function onBlockListViewAddBlockTypes(ViewEvent $event)
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