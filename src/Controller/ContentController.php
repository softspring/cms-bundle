<?php

namespace Softspring\CmsBundle\Controller;

use Softspring\CmsBundle\Manager\ContentVersionManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Render\ContentRender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends AbstractController
{
    protected ContentRender $contentRender;
    protected ContentVersionManagerInterface $contentVersionManager;

    public function __construct(ContentRender $contentRender, ContentVersionManagerInterface $contentVersionManager)
    {
        $this->contentRender = $contentRender;
        $this->contentVersionManager = $contentVersionManager;
    }

    public function renderRoutePath(RoutePathInterface $routePath, Request $request): Response
    {
        $content = $routePath->getRoute()->getContent();
        /** @var ?ContentVersionInterface $publishedVersion */
        $publishedVersion = $content->getPublishedVersion();

        if (!$publishedVersion) {
            throw $this->createNotFoundException();
        }

        $compiled = $publishedVersion->getCompiled();
        if (!isset($compiled["{$request->attributes->get('_sfs_cms_site')}"][$request->getLocale()])) {
            // if this content is not yet compiled, store it in database
            $compiled["{$request->attributes->get('_sfs_cms_site')}"][$request->getLocale()] = $this->contentRender->render($publishedVersion);
            $publishedVersion->setCompiled($compiled);
            $this->contentVersionManager->saveEntity($publishedVersion);
        }

        $pageContent = $compiled["{$request->attributes->get('_sfs_cms_site')}"][$request->getLocale()];

        // create response
        $response = new Response($pageContent);

        if ($routePath->getCacheTtl()) {
            $response->setPublic();
            $response->setMaxAge($routePath->getCacheTtl());
        }

        return $response;
    }
}
