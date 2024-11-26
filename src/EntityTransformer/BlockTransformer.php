<?php

namespace Softspring\CmsBundle\EntityTransformer;

use Doctrine\Persistence\ObjectManager;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidBlockException;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Utils\DataMigrator;

class BlockTransformer implements TransformerInterface
{
    use TransformEntityValuesTrait;

    public function __construct(protected CmsConfig $cmsConfig)
    {
    }

    /**
     * @throws UnsupportedException
     * @throws InvalidBlockException
     */
    public function transform(object $entity, ObjectManager $em): void
    {
        $block = $this->getBlock($entity);

        if (!$block->getData()) {
            return;
        }

        $blockConfig = $this->getBlockConfig($block);

        $extraData = $block->getData();
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->transformEntityValues($value, $em);
        }
        $extraData = DataMigrator::migrate($blockConfig['revision_migration_scripts'], $extraData, $blockConfig['revision'], $this->cmsConfig);
        $extraData['_revision'] = $blockConfig['revision'];
        $block->setData($extraData);
    }

    /**
     * @throws UnsupportedException
     * @throws InvalidBlockException
     */
    public function untransform(object $entity, ObjectManager $em): void
    {
        $block = $this->getBlock($entity);

        if (!$block->getData()) {
            return;
        }

        $blockConfig = $this->getBlockConfig($block);

        $extraData = DataMigrator::migrate($blockConfig['revision_migration_scripts'], $block->getData(), $blockConfig['revision'], $this->cmsConfig);
        foreach ($extraData as $field => $value) {
            $extraData[$field] = $this->untransformEntityValues($value, $em);
        }
        $block->setData($extraData);
    }

    /**
     * @throws UnsupportedException
     */
    protected function getBlock($entity): BlockInterface
    {
        if (!$entity instanceof BlockInterface) {
            throw new UnsupportedException(BlockInterface::class, get_class($entity));
        }

        return $entity;
    }

    /**
     * @throws InvalidBlockException
     */
    protected function getBlockConfig(BlockInterface $block): array
    {
        return $this->cmsConfig->getBlock($block->getType());
    }
}
