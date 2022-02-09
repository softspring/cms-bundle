<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\LayoutInterface;
use Softspring\CmsBundle\Model\PageInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CoreBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetEntityPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolveDoctrineTargetEntityPass extends AbstractResolveDoctrineTargetEntityPass
{
    /**
     * {@inheritDoc}
     */
    protected function getEntityManagerName(ContainerBuilder $container): string
    {
        return $container->getParameter('sfs_cms.entity_manager_name');
    }

    public function process(ContainerBuilder $container)
    {
        $this->setTargetEntityFromParameter('sfs_cms.site.class', SiteInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.block.class', BlockInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.layout.class', LayoutInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.route.class', RouteInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.route.path_class', RoutePathInterface::class, $container, true);
        $this->setTargetEntityFromParameter('sfs_cms.page.page_class', PageInterface::class, $container, true);
    }
}
