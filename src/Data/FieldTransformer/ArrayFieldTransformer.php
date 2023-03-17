<?php

namespace Softspring\CmsBundle\Data\FieldTransformer;

use Softspring\CmsBundle\Data\DataTransformer;
use Softspring\CmsBundle\Data\ReferencesRepository;

class ArrayFieldTransformer implements FieldTransformerInterface
{
    protected DataTransformer $dataTransformer;

    public function __construct(DataTransformer $dataTransformer)
    {
        $this->dataTransformer = $dataTransformer;
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsExport(string $type, mixed $data): bool
    {
        return is_array($data);
    }

    public function export(mixed $data, &$files = []): mixed
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->dataTransformer->export($value, $files);
        }

        return $data;
    }

    public function supportsImport(string $type, mixed $data): bool
    {
        return is_array($data);
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->dataTransformer->import($data, $referencesRepository, $options);
        }

        return $data;
    }
}
