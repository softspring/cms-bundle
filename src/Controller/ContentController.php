<?php

namespace Softspring\CmsBundle\Controller;

use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Render\ContentRender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends AbstractController
{
    protected ContentRender $contentRender;

    public function __construct(ContentRender $contentRender)
    {
        $this->contentRender = $contentRender;
    }

    /**
     * // TODO MATCH ALSO WITH SITE
     */
    public function renderRoutePath(RoutePathInterface $routePath): Response
    {
        $content = $routePath->getRoute()->getContent();

        $response = new Response($this->contentRender->render($content->getVersions()->first()));

        if ($routePath->getCacheTtl()) {
            $response->setPublic();
            $response->setMaxAge($routePath->getCacheTtl());
        }

        return $response;
    }
}
