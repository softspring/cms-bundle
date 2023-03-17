<?php

namespace Softspring\CmsBundle\Data\FieldTransformer;

use Softspring\CmsBundle\Data\ReferencesRepository;

interface FieldTransformerInterface
{
    public static function getPriority(): int;

    public function supportsExport(string $type, $data = null): bool;

    public function supportsImport(string $type, $data = null): bool;

    public function export(mixed $data, &$files = []): mixed;

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed;
}
