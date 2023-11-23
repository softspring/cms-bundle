<?php

namespace Softspring\CmsBundle\Plugin\Compiler;

use Softspring\Component\DoctrineTargetEntityResolver\DependencyInjection\Compiler\AbstractResolveDoctrineTargetEntityPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolvePluginDoctrineTargetEntityPass extends AbstractResolveDoctrineTargetEntityPass
{
    public function __construct(
        protected array $targetEntities = [],
    )
    {
    }

    protected function getEntityManagerName(ContainerBuilder $container): string
    {
        return $container->getParameter('sfs_cms.entity_manager_name');
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->targetEntities as $targetEntity) {
            if (!isset($targetEntity['parameterName'])) {
                throw new \InvalidArgumentException('Target entity configuration must have a parameterName');
            }

            if (!isset($targetEntity['interface'])) {
                throw new \InvalidArgumentException('Target entity configuration must have a interface');
            }

            $required = isset($targetEntity['required']) ? $targetEntity['required'] : false;

            $this->setTargetEntityFromParameter($targetEntity['parameterName'], $targetEntity['interface'], $container, $required);
        }
    }
}
