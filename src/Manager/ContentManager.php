<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class ContentManager implements ContentManagerInterface
{
    use CrudlEntityManagerTrait;

    protected EntityManagerInterface $em;
    protected ContentVersionManagerInterface $contentVersionManager;
    protected CmsConfig $cmsConfig;

    public function __construct(EntityManagerInterface $em, ContentVersionManagerInterface $contentVersionManager, CmsConfig $cmsConfig)
    {
        $this->em = $em;
        $this->contentVersionManager = $contentVersionManager;
        $this->cmsConfig = $cmsConfig;
    }

    public function getTargetClass(): string
    {
        return ContentInterface::class;
    }

    public function createEntity(?string $type = null): object
    {
        $class = $this->getTypeClass($type);

        /** @var ContentInterface $content */
        $content = new $class();
        $content->addVersion($version = $this->contentVersionManager->createEntity());
        $content->setLastVersionNumber(0);
        $version->setVersionNumber(0);
        $version->setLayout($this->getTypeDefaultLayout($type));

        return $content;
    }

    public function getRepository(?string $type = null): EntityRepository
    {
        /** @var EntityRepository $repo */
        $repo = $this->em->getRepository($this->getTypeClass($type));

        return $repo;
    }

    public function createVersion(ContentInterface $content, ContentVersionInterface $prevVersion = null): ContentVersionInterface
    {
        $version = $this->contentVersionManager->createEntity();
        $version->setLayout('default');

        if (!$prevVersion && $content->getVersions()->count()) {
            /** @var ContentVersionInterface $prevVersion */
            $prevVersion = $content->getVersions()->first();
        }

        if ($prevVersion) {
            $prevVersion->getLayout() && $version->setLayout($prevVersion->getLayout());
            $version->setData($prevVersion->getData());
        }

        $content->setLastVersionNumber((int)$content->getLastVersionNumber() +1);
        $version->setVersionNumber($content->getLastVersionNumber());

        $content->addVersion($version);

        return $version;
    }

    protected function getTypeClass(?string $type = null): string
    {
        return $this->getTypeConfig($type)['entity_class'];
    }

    protected function getTypeDefaultLayout(?string $type = null): string
    {
        return $this->getTypeConfig($type)['default_layout'];
    }

    protected function getTypeConfig(?string $type = null): array
    {
        if (!$type) {
            throw new \Exception('type is required');
        }

        return $this->cmsConfig->getContent($type, true);
    }
}
