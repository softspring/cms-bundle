<?php

namespace Softspring\CmsBundle\Controller;

use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Render\PageRender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ContentRenderer extends AbstractController
{
    /**
     * @var PageRender
     */
    protected $pageRender;

    /**
     * @param PageRender $pageRender
     */
    public function __construct(PageRender $pageRender)
    {
        $this->pageRender = $pageRender;
    }

    public function contentRender(RoutePathInterface $routePath): Response
    {
        $page = $routePath->getRoute()->getPage();

        $response = new Response($this->pageRender->render($page));

        if ($routePath->getCacheTtl()) {
            $response->setClientTtl($routePath->getCacheTtl());
        }

        return $response;
    }
}