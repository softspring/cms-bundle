<?php

namespace Softspring\CmsBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Config\Exception\InvalidLayoutException;
use Softspring\CmsBundle\Helper\CmsHelper;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class ContentManager implements ContentManagerInterface
{
    use CrudlEntityManagerTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected ContentVersionManagerInterface $contentVersionManager,
        protected CmsHelper $cmsHelper,
    ) {
    }

    public function getTargetClass(): string
    {
        return ContentInterface::class;
    }

    public function createEntity(string $type = null): object
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

    public function getRepository(string $type = null): EntityRepository
    {
        /** @var EntityRepository $repo */
        $repo = $this->em->getRepository($this->getTypeClass($type));

        return $repo;
    }

    /**
     * @throws InvalidLayoutException
     * @throws InvalidContentException
     */
    public function createVersion(ContentInterface $content, ContentVersionInterface $prevVersion = null, ?int $origin = ContentVersionInterface::ORIGIN_UNKNOWN): ContentVersionInterface
    {
        $version = $this->contentVersionManager->createEntity();
        $version->setLayout($this->cmsHelper->layout()->getDefaultLayout($content));

        if (!$prevVersion && $content->getLastVersion()) {
            $prevVersion = $content->getLastVersion();
        }

        if ($prevVersion) {
            $prevVersion->getLayout() && $version->setLayout($prevVersion->getLayout());
            $version->setData($prevVersion->getData());
        }

        $content->setLastVersionNumber((int) $content->getLastVersionNumber() + 1);
        $version->setVersionNumber($content->getLastVersionNumber());
        $version->setOrigin($origin);

        $content->addVersion($version);
        $content->setLastVersion($version);

        return $version;
    }

    public function getTypeClass(string $type = null): string
    {
        return $this->getTypeConfig($type)['entity_class'];
    }

    public function getType(mixed $objectOrClassName = null): string
    {
        $className = is_object($objectOrClassName) ? get_class($objectOrClassName) : $objectOrClassName;

        foreach ($this->cmsHelper->config()->getContents() as $type => $config) {
            if ($config['entity_class'] === $className) {
                return $type;
            }
        }

        throw new \Exception(sprintf('Content type not found for class "%s"', $className));
    }

    protected function getTypeDefaultLayout(string $type = null): string
    {
        return $this->getTypeConfig($type)['default_layout'];
    }

    protected function getTypeConfig(string $type = null): array
    {
        if (!$type) {
            throw new \Exception('type is required');
        }

        return $this->cmsHelper->config()->getContent($type, true);
    }
}
