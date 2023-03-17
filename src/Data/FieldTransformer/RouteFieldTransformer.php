<?php

namespace Softspring\CmsBundle\Data\FieldTransformer;

use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\CmsBundle\Model\RouteInterface;

class RouteFieldTransformer implements FieldTransformerInterface
{
    public static function getPriority(): int
    {
        return 100;
    }

    public function supportsExport(string $type, $data = null): bool
    {
        return $data instanceof RouteInterface;
    }

    public function supportsImport(string $type, $data = null): bool
    {
        return isset($data['_reference']) && str_starts_with($data['_reference'], 'route___');
    }

    public function export(mixed $data, &$files = []): mixed
    {
        return [
            '_reference' => 'route___'.$data->getId(),
        ];
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return $data;
    }
}
