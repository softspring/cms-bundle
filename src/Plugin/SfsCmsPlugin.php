<?php

namespace Softspring\CmsBundle\Plugin;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Softspring\CmsBundle\Plugin\Compiler\ResolvePluginDoctrineTargetEntityPass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SfsCmsPlugin extends Bundle
{
    /**
     * @throws \Exception
     */
    public function getPath(): string
    {
        throw new \Exception('You must override getPath() method in your plugin class.');
    }

    protected function getTargetEntities(): array
    {
        return [];
    }

    protected function getTargetEntitiesMappings(): array
    {
        return [];
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        foreach ($this->getTargetEntitiesMappings() as $path => $namespace) {
            $this->addRegisterMappingsPass($container, [$path => $namespace]);
        }

        $container->addCompilerPass(new ResolvePluginDoctrineTargetEntityPass($this->getTargetEntities()));
    }

    protected function addRegisterMappingsPass(ContainerBuilder $container, array $mappings, $enablingParameter = false): void
    {
        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, ['sfs_cms.entity_manager_name'], $enablingParameter));
    }

    /**
     * Returns the bundle's container extension.
     *
     * @throws \LogicException
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $extension = $this->createContainerExtension();

            if (null !== $extension) {
                if (!$extension instanceof ExtensionInterface) {
                    throw new \LogicException(sprintf('Extension "%s" must implement Symfony\Component\DependencyInjection\Extension\ExtensionInterface.', get_debug_type($extension)));
                }

                // check naming convention
                $basename = preg_replace('/Plugin$/', '', $this->getName());
                $expectedAlias = Container::underscore($basename);

                if ($expectedAlias != $extension->getAlias()) {
                    throw new \LogicException(sprintf('Users will expect the alias of the default extension of a bundle to be the underscored version of the bundle name ("%s"). You can override "Plugin::getContainerExtension()" if you want to use "%s" or another alias.', $expectedAlias, $extension->getAlias()));
                }

                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }

        return $this->extension ?: null;
    }

    protected function getContainerExtensionClass(): string
    {
        $basename = preg_replace('/Plugin$/', '', $this->getName());

        return $this->getNamespace().'\\DependencyInjection\\'.$basename.'Extension';
    }
}
