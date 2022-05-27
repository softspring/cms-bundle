<?php

namespace Softspring\CmsBundle\Controller;

use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Render\ContentRender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends AbstractController
{
    protected ContentRender $contentRender;

    public function __construct(ContentRender $contentRender)
    {
        $this->contentRender = $contentRender;
    }

    /**
     * // TODO MATCH ALSO WITH SITE.
     */
    public function renderRoutePath(RoutePathInterface $routePath, Request $request): Response
    {
        $content = $routePath->getRoute()->getContent();
        /** @var ContentVersionInterface $publishedVersion */
        $publishedVersion = $content->getPublishedVersion();

        if (!$publishedVersion) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $pageContent = $publishedVersion->getCompiled()[$request->getLocale()] ?? $this->contentRender->render($publishedVersion);

        // create response
        $response = new Response($pageContent);

        if ($routePath->getCacheTtl()) {
            $response->setPublic();
            $response->setMaxAge($routePath->getCacheTtl());
        }

        return $response;
    }
}
