<?php

namespace Softspring\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends AbstractController
{
    public function redirection(string $route, int $statusCode = Response::HTTP_PERMANENTLY_REDIRECT): RedirectResponse
    {
        return $this->redirectToRoute($route, [], $statusCode);
    }

    public function redirectToUrl(string $url, int $statusCode = Response::HTTP_PERMANENTLY_REDIRECT): RedirectResponse
    {
        return $this->redirect($url, $statusCode);
    }
}
