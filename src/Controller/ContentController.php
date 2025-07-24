<?php

namespace Softspring\CmsBundle\Controller;

use Softspring\CmsBundle\Compiler\ContentVersionCompiler;
use Softspring\CmsBundle\Manager\CompiledDataManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends AbstractController
{
    public function __construct(
        protected ContentVersionManagerInterface $contentVersionManager,
        protected ContentVersionCompiler $contentVersionCompiler,
        protected CompiledDataManagerInterface $compiledDataManager,
        protected string $contentCacheType,
    ) {
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
            $compileKey = $this->compiledDataManager->getCompileKey($publishedVersion, $request);
            $response->setEtag(md5($content->getId().$content->getLastModified()?->getTimestamp().$compileKey));
            $response->setLastModified($content->getLastModified());
            // Set response as public. Otherwise it will be private by default.
            $response->setPublic();
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        $pageContent = $this->contentVersionManager->getCompiledContent($publishedVersion, $request);

        // create response
        $response->setContent($pageContent->getDataPart('content'));

        if ($pageContent->hasErrors()) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        } elseif ('ttl' === $this->contentCacheType && $routePath->getCacheTtl()) {
            $response->setPublic();
            $response->setMaxAge($routePath->getCacheTtl());
        }

        return $response;
    }
}
