<?php

namespace Softspring\CmsBundle\Data\FieldTransformer;

use Softspring\CmsBundle\Data\ReferencesRepository;

class DefaultTransformer implements FieldTransformerInterface
{
    public static function getPriority(): int
    {
        return -255;
    }

    public function supportsExport(string $type, $data = null): bool
    {
        return true;
    }

    public function supportsImport(string $type, $data = null): bool
    {
        return true;
    }

    public function export(mixed $data, &$files = []): mixed
    {
        return $data;
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return $data;
    }
}
