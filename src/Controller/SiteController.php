<?php

namespace Softspring\CmsBundle\Controller;

use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SiteController extends AbstractController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function staticRobotsTxt(SiteInterface $site, Environment $twig): Response
    {
        // TODO LOAD WITHOUT TWIG IF NOT A TWIG TEMPLATE
        $robotsContent = $twig->render($site->getConfig()['robots']['static_file']);

        return new Response($robotsContent, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
