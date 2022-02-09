<?php

namespace Softspring\CmsBundle\Controller\Admin;

use App\Entity\Page;
use Softspring\CmsBundle\Model\LayoutInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LayoutController extends AbstractController
{
    public function preview(LayoutInterface $layout, Request $request): Response
    {
        $request->setLocale($request->query->get('locale', 'es'));

        $emptyPage = new Page(); // TODO change this with manager to create exact class object
        $emptyPage->_generateId();

        return $this->render($layout->getTemplate(), [
            'page' => $emptyPage,
            'content' => '',
        ]);
    }
}