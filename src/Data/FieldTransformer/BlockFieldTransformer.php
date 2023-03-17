<?php

namespace Softspring\CmsBundle\Data\FieldTransformer;

use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Utils\Slugger;

class BlockFieldTransformer implements FieldTransformerInterface
{
    public static function getPriority(): int
    {
        return 100;
    }

    public function supportsExport(string $type, $data = null): bool
    {
        return $data instanceof BlockInterface;
    }

    public function supportsImport(string $type, $data = null): bool
    {
        return isset($data['_reference']) && str_starts_with($data['_reference'], 'block___');
    }

    public function export(mixed $data, &$files = []): mixed
    {
        return [
            '_reference' => 'block___'.Slugger::lowerSlug($data->getName()),
        ];
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return $data;
    }
}
