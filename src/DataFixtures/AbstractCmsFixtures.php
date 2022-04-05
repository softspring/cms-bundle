<?php

namespace Softspring\CmsBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\RoutePathManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;

abstract class AbstractCmsFixtures extends Fixture
{
    protected ContentManagerInterface $contentManager;
    protected RouteManagerInterface $routeManager;
    protected RoutePathManagerInterface $routePathManager;

    public function __construct(ContentManagerInterface $contentManager, RouteManagerInterface $routeManager, RoutePathManagerInterface $routePathManager)
    {
        $this->contentManager = $contentManager;
        $this->routeManager = $routeManager;
        $this->routePathManager = $routePathManager;
    }

    protected function createPage(string $name, string $layout = null, array $data = null, array $extraData = []): ContentInterface
    {
        return $this->createContent('page', $name, $layout, $data, $extraData);
    }

    protected function createContent(string $contentType, string $name, string $layout = null, array $data = null, array $extraData = []): ContentInterface
    {
        $content = $this->contentManager->createEntity($contentType);
        $content->setName($name);
        $content->setExtraData($extraData);

        if ($layout && $data) {
            $this->createVersion($content, $layout, $data);
        }

        return $content;
    }

    protected function createVersion(ContentInterface $content, string $layout, array $data): ContentVersionInterface
    {
        $version = $this->contentManager->createVersion($content);
        $version->setLayout($layout);
        $version->setData($data);

        return $version;
    }
}