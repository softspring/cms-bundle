<?php

namespace Softspring\CmsBundle\Data;

use Softspring\CmsBundle\Data\FieldTransformer\FieldTransformerInterface;

class DataTransformer
{
    /**
     * @var FieldTransformerInterface[]
     */
    protected iterable $fieldTransformers;

    /**
     * @param FieldTransformerInterface[] $fieldTransformers
     */
    public function __construct(iterable $fieldTransformers)
    {
        $this->fieldTransformers = $fieldTransformers;
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options): mixed
    {
        foreach ($this->fieldTransformers as $fieldTransformer) {
            if ($fieldTransformer->supportsImport('', $data)) {
                return $fieldTransformer->import($data, $referencesRepository, $options);
            }
        }

        return $data;
    }

    public function export(mixed $data, &$files = []): mixed
    {
        foreach ($this->fieldTransformers as $fieldTransformer) {
            if ($fieldTransformer->supportsExport('', $data)) {
                return $fieldTransformer->export($data, $files);
            }
        }

        return $data;
    }
}
