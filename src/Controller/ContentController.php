<?php

namespace Softspring\CmsBundle\Controller;

use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Render\ContentVersionRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends AbstractController
{
    public function __construct(
        protected ContentVersionRenderer $contentRender,
        protected ContentManagerInterface $contentManager,
        protected ContentVersionManagerInterface $contentVersionManager,
        protected string $prefixCompiled,
    ) {
    }

    public function renderRoutePath(RoutePathInterface $routePath, Request $request): Response
    {
        $content = $routePath->getRoute()->getContent();
        /** @var ?ContentVersionInterface $publishedVersion */
        $publishedVersion = $content->getPublishedVersion();

        if (!$publishedVersion) {
            throw $this->createNotFoundException();
        }

        $canSaveCompiled = $this->contentVersionManager->canSaveCompiled($publishedVersion);
        $compiled = $publishedVersion->getCompiled();
        $compiledKey = "{$this->prefixCompiled}{$request->attributes->get('_sfs_cms_site')}/{$request->getLocale()}";
        if (!isset($compiled[$compiledKey]) || !$canSaveCompiled) {
            // if this content is not yet compiled, store it in database
            $compiled[$compiledKey] = $this->contentRender->render($publishedVersion);

            if ($canSaveCompiled) {
                $publishedVersion->setCompiled($compiled);
                $this->contentVersionManager->saveEntity($publishedVersion);
            }
        }

        $pageContent = $compiled[$compiledKey];

        // create response
        $response = new Response($pageContent);

        if ($routePath->getCacheTtl()) {
            $response->setPublic();
            $response->setMaxAge($routePath->getCacheTtl());
        }

        return $response;
    }
}
