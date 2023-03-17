<?php

namespace Softspring\CmsBundle\Data;

use Softspring\CmsBundle\Data\EntityTransformer\EntityTransformerInterface;
use Softspring\CmsBundle\Data\Exception\DataTransformerNotFoundException;

abstract class AbstractDataImportExport
{
    /**
     * @var EntityTransformerInterface[]
     */
    protected iterable $entityTransformers;

    /**
     * @param EntityTransformerInterface[] $entityTransformers
     */
    public function __construct(iterable $entityTransformers)
    {
        $this->entityTransformers = $entityTransformers;
    }

    /**
     * @throws DataTransformerNotFoundException
     */
    protected function getDataTransformer(string $type, $data = null): EntityTransformerInterface
    {
        /** @var EntityTransformerInterface $transformer */
        foreach ($this->entityTransformers as $transformer) {
            if ($transformer->supports($type, $data)) {
                return $transformer;
            }
        }

        throw new DataTransformerNotFoundException($type);
    }
}
