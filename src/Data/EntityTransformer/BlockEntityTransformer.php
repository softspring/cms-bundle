<?php

namespace Softspring\CmsBundle\Data\EntityTransformer;

use Softspring\CmsBundle\Data\DataTransformer;
use Softspring\CmsBundle\Data\Exception\InvalidElementException;
use Softspring\CmsBundle\Data\Exception\ReferenceNotFoundException;
use Softspring\CmsBundle\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Utils\Slugger;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
class BlockEntityTransformer implements EntityTransformerInterface
{
    protected BlockManagerInterface $blockManager;
    protected DataTransformer $dataTransformer;

    public function __construct(BlockManagerInterface $blockManager, DataTransformer $dataTransformer)
    {
        $this->blockManager = $blockManager;
        $this->dataTransformer = $dataTransformer;
    }

    public static function getPriority(): int
    {
        return 0;
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
                'publish_start_date' => $block->getPublishStartDate() ? $block->getPublishStartDate()->format('Y-m-d H:i:s') : null,
                'publish_end_date' => $block->getPublishEndDate() ? $block->getPublishEndDate()->format('Y-m-d H:i:s') : null,
                'data' => $this->dataTransformer->export($block->getData(), $files),
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
        $block->setPublishStartDate($data['block']['publish_start_date'] ? new \DateTime($data['block']['publish_start_date']) : null);
        $block->setPublishEndDate($data['block']['publish_end_date'] ? new \DateTime($data['block']['publish_end_date']) : null);

        return $block;
    }
}
