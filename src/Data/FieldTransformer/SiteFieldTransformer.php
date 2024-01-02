<?php

namespace Softspring\CmsBundle\Data\FieldTransformer;

use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Utils\Slugger;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
class SiteFieldTransformer implements FieldTransformerInterface
{
    public static function getPriority(): int
    {
        return 100;
    }

    public function supportsExport(string $type, mixed $data): bool
    {
        return $data instanceof SiteInterface;
    }

    public function export(mixed $data, &$files = []): mixed
    {
        return [
            '_reference' => 'site___'.Slugger::lowerSlug($data->getId()),
        ];
    }

    public function supportsImport(string $type, mixed $data): bool
    {
        return isset($data['_reference']) && str_starts_with($data['_reference'], 'site___');
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return $referencesRepository->getReference($data['_reference'], true);
    }
}
