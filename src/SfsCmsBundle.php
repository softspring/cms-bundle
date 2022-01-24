<?php

namespace Softspring\CmsBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\ORM\Events;
use Softspring\CmsBundle\DependencyInjection\Compiler\AliasDoctrineEntityManagerPass;
use Softspring\CmsBundle\DependencyInjection\Compiler\ModuleRenderPass;
use Softspring\CmsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetEntityPass;
use Softspring\CmsBundle\EntityListener\ModuleDiscriminatorMapListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SfsCmsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $basePath = realpath(__DIR__.'/../config/doctrine-mapping/');

        $this->addRegisterMappingsPass($container, [$basePath => 'Softspring\CmsBundle\Model']);

        $container->addCompilerPass(new AliasDoctrineEntityManagerPass());
        $container->addCompilerPass(new ResolveDoctrineTargetEntityPass());
        $container->addCompilerPass(new ModuleRenderPass());
    }

    private function addRegisterMappingsPass(ContainerBuilder $container, array $mappings, $enablingParameter = false)
    {
        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, ['sfs_cms.entity_manager_name'], $enablingParameter));
    }

    public function boot()
    {
        $listener = $this->container->get(ModuleDiscriminatorMapListener::class);
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $evm = $em->getEventManager();
        $evm->addEventListener(Events::loadClassMetadata, $listener);
    }
}