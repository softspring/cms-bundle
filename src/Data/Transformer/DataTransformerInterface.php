<?php

namespace Softspring\CmsBundle\Data\Transformer;

use Softspring\CmsBundle\Data\Exception\InvalidElementException;
use Softspring\CmsBundle\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsBundle\Data\ReferencesRepository;

interface DataTransformerInterface
{
    public static function getPriority(): int;

    public function supports(string $type, $data = null): bool;

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
