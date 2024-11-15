<?php

namespace Softspring\CmsBundle\Data\FieldTransformer;

use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\TranslatableBundle\Model\Translation;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
class TranslationFieldTransformer implements FieldTransformerInterface
{
    public static function getPriority(): int
    {
        return 100;
    }

    public function supportsExport(string $type, mixed $data): bool
    {
        return $data instanceof Translation;
    }

    /**
     * @param Translation $data
     * @return array
     */
    public function export(mixed $data, &$files = []): mixed
    {
        return $data->__toArray();
    }

    public function supportsImport(string $type, mixed $data): bool
    {
        return isset($data['_trans_id']);
    }

    /**
     * @param array $data
     * @return Translation
     */
    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return Translation::createFromArray($data);
    }
}
