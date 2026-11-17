<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Config\Model;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

final class MetadataExtension implements ConfigExtensionInterface
{
    public function extend(NodeDefinition $rootNode): void
    {
        if (!$rootNode instanceof ArrayNodeDefinition || isset($rootNode->getChildNodeDefinitions()['meta'])) {
            return;
        }
        $metadata = new ArrayNodeDefinition('meta');
        $metadata->variablePrototype();
        $rootNode->append($metadata);
    }

    public function supports(string $modelClassName): bool
    {
        return true;
    }
}
