<?php

namespace Softspring\CmsBundle\Controller\Admin;

use Softspring\CmsBundle\Model\PageInterface;
use Softspring\CmsBundle\Render\PageRender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends AbstractController
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

    public function preview(PageInterface $page, Request $request): Response
    {
        $request->setLocale($request->query->get('locale', 'es'));
        return new Response($this->pageRender->render($page));
    }
}