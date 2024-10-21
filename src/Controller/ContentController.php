<?php

namespace Softspring\CmsBundle\Controller;

use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Render\ContentVersionCompiler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends AbstractController
{
    public function __construct(protected ContentVersionManagerInterface $contentVersionManager, protected ContentVersionCompiler $contentVersionCompiler, protected string $contentCacheType)
    {
    }

    public function renderRoutePath(RoutePathInterface $routePath, Request $request): Response
    {
        $content = $routePath->getRoute()->getContent();

        $response = new Response();

        /** @var ?ContentVersionInterface $publishedVersion */
        $publishedVersion = $content->getPublishedVersion();

        if (!$publishedVersion) {
            throw $this->createNotFoundException();
        }

        if ('last_modified' === $this->contentCacheType) {
            $response->setEtag(md5($content->getId().$content->getLastModified()?->getTimestamp().$this->contentVersionCompiler->getCompileKeyFromRequest($publishedVersion, $request)));
            $response->setLastModified($content->getLastModified());
            // Set response as public. Otherwise it will be private by default.
            $response->setPublic();
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        $pageContent = $this->contentVersionManager->getCompiledContent($publishedVersion, $request);

        // create response
        $response->setContent($pageContent);

        if ('ttl' === $this->contentCacheType && $routePath->getCacheTtl()) {
            $response->setPublic();
            $response->setMaxAge($routePath->getCacheTtl());
        }

        return $response;
    }
}
