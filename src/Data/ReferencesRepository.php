<?php

namespace Softspring\CmsBundle\Data;

use Softspring\CmsBundle\Data\Exception\ReferenceNotFoundException;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
class ReferencesRepository
{
    protected array $references = [];

    public function addReference(string $id, object $entity): void
    {
        $this->references[$id] = $entity;
    }

    /**
     * @throws ReferenceNotFoundException
     */
    public function getReference(string $id, bool $throwNotFound = false): ?object
    {
        $entity = $this->references[$id] ?? null;

        if (!$entity && $throwNotFound) {
            throw new ReferenceNotFoundException("Reference $id not found");
        }

        return $entity;
    }
}
