<?php

namespace Softspring\CmsBundle\Controller;

use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends AbstractController
{
    public function __construct(protected ContentVersionManagerInterface $contentVersionManager)
    {
    }

    public function renderRoutePath(RoutePathInterface $routePath, Request $request): Response
    {
        $content = $routePath->getRoute()->getContent();
        /** @var ?ContentVersionInterface $publishedVersion */
        $publishedVersion = $content->getPublishedVersion();

        if (!$publishedVersion) {
            throw $this->createNotFoundException();
        }

        $pageContent = $this->contentVersionManager->getCompiledContent($publishedVersion, $request);

        // create response
        $response = new Response($pageContent);

        if ($routePath->getCacheTtl()) {
            $response->setPublic();
            $response->setMaxAge($routePath->getCacheTtl());
        }

        return $response;
    }
}
