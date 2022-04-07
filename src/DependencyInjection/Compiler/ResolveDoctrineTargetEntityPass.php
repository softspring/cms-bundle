<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\MenuItemInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CoreBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetEntityPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolveDoctrineTargetEntityPass extends AbstractResolveDoctrineTargetEntityPass
{
    protected function getEntityManagerName(ContainerBuilder $container): string
    {
        return $container->getParameter('sfs_cms.entity_manager_name');
    }

    public function process(ContainerBuilder $container)
    {
//        $this->setTargetEntityFromParameter('sfs_cms.site.class', SiteInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.block.class', BlockInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.route.class', RouteInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.route.path_class', RoutePathInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.content.content_class', ContentInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.content.content_version_class', ContentVersionInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.menu.menu_class', MenuInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.menu.menu_item_class', MenuItemInterface::class, $container, true);
    }
}
