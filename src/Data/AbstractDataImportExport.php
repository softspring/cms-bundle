<?php

namespace Softspring\CmsBundle\Data;

use Softspring\CmsBundle\Data\Exception\DataTransformerNotFoundException;
use Softspring\CmsBundle\Data\Transformer\DataTransformerInterface;

abstract class AbstractDataImportExport
{
    /**
     * @var DataTransformerInterface[]
     */
    protected iterable $transformers;

    /**
     * @param DataTransformerInterface[] $transformers
     */
    public function __construct(iterable $transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * @throws DataTransformerNotFoundException
     */
    protected function getDataTransformer(string $type, $data = null): DataTransformerInterface
    {
        /** @var DataTransformerInterface $transformer */
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($type, $data)) {
                return $transformer;
            }
        }

        throw new DataTransformerNotFoundException($type);
    }
}
