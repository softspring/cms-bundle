<?php

namespace Softspring\CmsBundle\Config\Model;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

interface ConfigExtensionInterface
{
    public function extend(NodeDefinition $rootNode): void;

    public function supports(string $modelClassName): bool;
}
