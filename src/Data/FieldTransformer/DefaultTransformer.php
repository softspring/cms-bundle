<?php

namespace Softspring\CmsBundle\Data\FieldTransformer;

use Softspring\CmsBundle\Data\ReferencesRepository;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
class DefaultTransformer implements FieldTransformerInterface
{
    public static function getPriority(): int
    {
        return -255;
    }

    public function supportsExport(string $type, mixed $data): bool
    {
        return true;
    }

    public function export(mixed $data, &$files = []): mixed
    {
        return $data;
    }

    public function supportsImport(string $type, mixed $data): bool
    {
        return true;
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return $data;
    }
}
