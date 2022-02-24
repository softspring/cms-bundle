<?php

namespace Softspring\CmsBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\TwigExtraBundle\Twig\ExtensibleAppVariable;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class SiteRequestListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    protected $siteRouteParamName;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var AppVariable
     */
    protected $twigAppVariable;

    /**
     * @var string
     */
    protected $findParamName;

    /**
     * SiteRequestListener constructor.
     * @param EntityManagerInterface $em
     * @param string $siteRouteParamName
     * @param RouterInterface $router
     * @param AppVariable $twigAppVariable
     * @param string $findParamName
     */
    public function __construct(EntityManagerInterface $em, string $siteRouteParamName, RouterInterface $router, AppVariable $twigAppVariable, string $findParamName)
    {
        $this->em = $em;
        $this->siteRouteParamName = $siteRouteParamName;
        $this->router = $router;
        $this->twigAppVariable = $twigAppVariable;
        $this->findParamName = $findParamName;

        if (!$this->twigAppVariable instanceof ExtensibleAppVariable) {
            throw new InvalidConfigurationException('You must configure SfsCoreBundle to extend twig app variable');
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onRequestGetSite', 30], // router listener has 32
            ],
        ];
    }

    /**
     * @param GetResponseEvent|RequestEvent $event
     * @throws UnauthorizedHttpException
     */
    public function onRequestGetSite($event)
    {
        $request = $event->getRequest();

        if ($request->attributes->has($this->siteRouteParamName)) {
            $site = $request->attributes->get($this->siteRouteParamName);

            if (!$site) {
                throw new NotFoundHttpException('Empty _site');
            }

            if (!$site instanceof SiteInterface) {
                $site = $this->em->getRepository(SiteInterface::class)->findOneBy([$this->findParamName => $site]);

                if (!$site) {
                    throw new NotFoundHttpException('Site not found');
                }
            }

            if (!$site->isEnabled()) {
                throw new NotFoundHttpException('Site is not enabled');
            }

            $request->attributes->set($this->siteRouteParamName, $site);

            $context = $this->router->getContext();
            $context->setParameter('_site', $site);

            $this->twigAppVariable->setSite($site);
        }
    }
}