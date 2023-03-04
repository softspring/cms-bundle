<?php

namespace Softspring\CmsBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Softspring\CmsBundle\DependencyInjection\Compiler\AddCollectionTranslationsPass;
use Softspring\CmsBundle\DependencyInjection\Compiler\AddTwigNamespacesPass;
use Softspring\CmsBundle\DependencyInjection\Compiler\AliasDoctrineEntityManagerPass;
use Softspring\CmsBundle\DependencyInjection\Compiler\InjectWebDebugToolbarListenerPass;
use Softspring\CmsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetEntityPass;
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

        $this->addRegisterMappingsPass($container, ["$basePath/model" => 'Softspring\CmsBundle\Model']);
        $this->addRegisterMappingsPass($container, ["$basePath/entities" => 'Softspring\CmsBundle\Entity']);

        $container->addCompilerPass(new AliasDoctrineEntityManagerPass());
        $container->addCompilerPass(new ResolveDoctrineTargetEntityPass());
        $container->addCompilerPass(new InjectWebDebugToolbarListenerPass());
        $container->addCompilerPass(new AddTwigNamespacesPass());
        $container->addCompilerPass(new AddCollectionTranslationsPass());
    }

    private function addRegisterMappingsPass(ContainerBuilder $container, array $mappings, $enablingParameter = false)
    {
        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, ['sfs_cms.entity_manager_name'], $enablingParameter));
    }
}
