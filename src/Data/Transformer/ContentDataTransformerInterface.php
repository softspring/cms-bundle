<?php

namespace Softspring\CmsBundle\Data\Transformer;

use Softspring\CmsBundle\Data\Exception\InvalidElementException;
use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\CmsBundle\Model\ContentInterface;

interface ContentDataTransformerInterface extends DataTransformerInterface
{
    /**
     * @throws InvalidElementException
     */
    public function export(object $element, &$files = [], object $contentVersion = null, string $contentType = null): array;

    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): ContentInterface;
}
