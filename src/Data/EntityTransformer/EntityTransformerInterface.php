<?php

namespace Softspring\CmsBundle\Data\EntityTransformer;

use Softspring\CmsBundle\Data\Exception\InvalidElementException;
use Softspring\CmsBundle\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsBundle\Data\ReferencesRepository;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
interface EntityTransformerInterface
{
    public static function getPriority(): int;

    public function supports(string $fieldName, $fieldValue = null): bool;

    /**
     * @throws InvalidElementException
     */
    public function export(object $element, &$files = []): array;

    public function preload(array $data, ReferencesRepository $referencesRepository): void;

    /**
     * @throws RunPreloadBeforeImportException
     */
    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): object;
}
