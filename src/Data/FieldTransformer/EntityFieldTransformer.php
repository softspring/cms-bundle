<?php

namespace Softspring\CmsBundle\Data\FieldTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use Softspring\CmsBundle\Data\ReferencesRepository;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
class EntityFieldTransformer implements FieldTransformerInterface
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getPriority(): int
    {
        return 10;
    }

    public function supportsExport(string $type, mixed $data): bool
    {
        try {
            if (!is_object($data)) {
                return false;
            }

            $this->em->getClassMetadata(get_class($data));

            return true;
        } catch (MappingException) {
            return false;
        }
    }

    public function export(mixed $data, &$files = []): mixed
    {
        return [
            '_entity' => [
                'class' => $this->em->getClassMetadata(get_class($data))->getName(),
                'id' => $this->em->getUnitOfWork()->getEntityIdentifier($data),
            ],
        ];
    }

    public function supportsImport(string $type, mixed $data): bool
    {
        return isset($data['_entity']) && isset($data['_entity']['class']) && isset($data['_entity']['id']);
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return $this->em->getRepository($data['_entity']['class'])->findOneBy($data['_entity']['id']);
    }
}
