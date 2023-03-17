<?php

namespace Softspring\CmsBundle\Data;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Data\EntityTransformer\EntityTransformerInterface;
use Softspring\CmsBundle\Data\Exception\DataTransformerNotFoundException;

class DataImporter extends AbstractDataImportExport
{
    protected EntityManagerInterface $em;

    protected ReferencesRepository $referenceRepository;

    /**
     * @param EntityTransformerInterface[] $entityTransformers
     */
    public function __construct(EntityManagerInterface $em, iterable $entityTransformers)
    {
        $this->em = $em;
        parent::__construct($entityTransformers);
        $this->referenceRepository = new ReferencesRepository();
    }

    /**
     * @throws DataTransformerNotFoundException
     */
    public function import(array $contents, array $options = []): void
    {
        // do preloading
        foreach ($contents as $type => $elements) {
            foreach ($elements as $data) {
                $this->getDataTransformer($type, $data)->preload($data, $this->referenceRepository);
            }
        }

        // import medias before to prevent errors
        foreach ($contents['media'] ?? [] as $data) {
            $entity = $this->getDataTransformer('media', $data)->import($data, $this->referenceRepository, $options);
            $this->em->persist($entity);
        }
        $this->em->flush();
        unset($contents['media']);

        // import rest of contents
        foreach ($contents as $type => $elements) {
            foreach ($elements as $data) {
                $entity = $this->getDataTransformer($type, $data)->import($data, $this->referenceRepository, $options);
                $this->em->persist($entity);
            }
        }
        $this->em->flush();
    }
}
