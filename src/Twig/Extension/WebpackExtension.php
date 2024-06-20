<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WebpackExtension extends AbstractExtension
{
    public function __construct(protected ?EntrypointLookupCollectionInterface $entrypointLookupCollection = null)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_encore_entry_reset', [$this, 'webpackResetEntrypoint']),
        ];
    }

    public function webpackResetEntrypoint(string $entrypointName = '_default'): void
    {
        if (!$this->entrypointLookupCollection) {
            throw new \RuntimeException('Webpack encore entrypoint lookup collection is not available');
        }

        $this->entrypointLookupCollection->getEntrypointLookup($entrypointName)->reset();
    }
}
