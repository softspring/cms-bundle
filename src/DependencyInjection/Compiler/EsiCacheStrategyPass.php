<?php

namespace Softspring\CmsBundle\DependencyInjection\Compiler;

use Softspring\CmsBundle\HttpCache\ShortestResponseCacheStrategy;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\HttpCache\ResponseCacheStrategy;

class EsiCacheStrategyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('esi')) {
            return;
        }

        $esiConfig = $container->getParameter('sfs_cms.esi');

        if (!$esiConfig['enabled']) {
            return;
        }

        $definition = $container->findDefinition('esi');
        $definition->setClass('Softspring\CmsBundle\HttpCache\Esi');
        $definition->addArgument(['text/html', 'text/xml', 'application/xhtml+xml', 'application/xml']);
        $definition->addArgument(ResponseCacheStrategy::class);

        if ('shortest_response' === $esiConfig['response_cache_strategy']) {
            $definition->setArgument(1, ShortestResponseCacheStrategy::class);
        }
    }
}
