<?php

namespace Softspring\CmsBundle\Data\Transformer;

use Softspring\CmsBundle\Data\Exception\InvalidElementException;
use Softspring\CmsBundle\Data\Exception\ReferenceNotFoundException;
use Softspring\CmsBundle\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Utils\Slugger;

class BlockTransformer extends AbstractDataTransformer
{
    protected BlockManagerInterface $blockManager;

    public function __construct(BlockManagerInterface $blockManager)
    {
        $this->blockManager = $blockManager;
    }

    public function supports(string $type, $data = null): bool
    {
        if ('blocks' === $type) {
            return true;
        }

        return false;
    }

    public function export(object $element, &$files = []): array
    {
        if (!$element instanceof BlockInterface) {
            throw new InvalidElementException(sprintf('%s dumper requires that $element to be an instance of %s, %s given.', get_called_class(), BlockInterface::class, get_class($element)));
        }

        $block = $element;

        return [
            'block' => [
                'type' => $block->getType(),
                'name' => $block->getName(),
                'data' => $this->exportData($block->getData(), $this->blockManager->getEntityManager(), $files),
            ],
        ];
    }

    public function preload(array $data, ReferencesRepository $referencesRepository): void
    {
        $blockId = Slugger::lowerSlug($data['block']['name']);
        $referencesRepository->addReference("block___{$blockId}", $this->blockManager->createEntity($data['block']['type']));
    }

    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): BlockInterface
    {
        try {
            $blockId = Slugger::lowerSlug($data['block']['name']);
            /** @var BlockInterface $block */
            $block = $referencesRepository->getReference("block___{$blockId}", true);
        } catch (ReferenceNotFoundException $e) {
            throw new RunPreloadBeforeImportException('You must call preload() method before importing', 0, $e);
        }

        $block->setName($data['block']['name']);
        $block->setData($data['block']['data']);

        return $block;
    }
}
