<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouterExtension extends AbstractExtension
{
    protected RequestStack $requestStack;
    protected RouteManagerInterface $routeManager;

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_url', [$this, 'getUrl']),
            new TwigFunction('sfs_cms_path', [$this, 'getPath']),
        ];
    }

    /**
     * @param string|RouteInterface $route
     *
     * @throws \Exception
     */
    public function getUrl($route): string
    {
        if ($this->isPreview()) {
            return '#';
        }

        if (! ($route = $this->getRoute($route))) {
            throw new \Exception('Route not found');
        }

        $site = $this->getSite();

        return '#TODO: generate route url';
    }

    /**
     * @param string|RouteInterface $route
     *
     * @throws \Exception
     */
    public function getPath($route): string
    {
        if ($this->isPreview()) {
            return '#';
        }

        if (! ($route = $this->getRoute($route))) {
            throw new \Exception('Route not found');
        }

        $site = $this->getSite();

        return '#TODO: generate route path';
    }

    protected function getRoute($route): ?RouteInterface
    {
        if (is_string($route)) {
            return $this->routeManager->getRepository()->findOneById($route);
        }

        if (!$route instanceof RouteInterface) {
            throw new \Exception(sprintf('Provided route of %s class must be an instance of %s', get_class($route), RouteInterface::class));
        }

        return $route;
    }

    protected function getSite(): SiteInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        /** @var SiteInterface $site */
        $site = $request->attributes->get('_site');

        if (!$site) {
            throw new \Exception('Can not generate route because no site is selected');
        }

        return $site;
    }

    protected function isPreview(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->attributes->has('_cms_preview');
    }
}