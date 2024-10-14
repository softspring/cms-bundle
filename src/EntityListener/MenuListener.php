<?php

namespace Softspring\CmsBundle\EntityListener;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Softspring\CmsBundle\EntityTransformer\MenuTransformer;
use Softspring\CmsBundle\EntityTransformer\UnsupportedException;
use Softspring\CmsBundle\Model\MenuInterface;

class MenuListener
{
    protected MenuTransformer $menuTransformer;

    public function __construct(MenuTransformer $menuTransformer)
    {
        $this->menuTransformer = $menuTransformer;
    }

    /**
     * @throws UnsupportedException
     */
    public function postLoad(MenuInterface $content, PostLoadEventArgs $event): void
    {
        $this->menuTransformer->untransform($content, $event->getObjectManager());
    }

    /**
     * @throws UnsupportedException
     */
    public function preUpdate(MenuInterface $content, PreUpdateEventArgs $event): void
    {
        $this->menuTransformer->transform($content, $event->getObjectManager());
    }

    /**
     * @throws UnsupportedException
     */
    public function prePersist(MenuInterface $content, PrePersistEventArgs $event): void
    {
        $this->menuTransformer->transform($content, $event->getObjectManager());
    }
}
