<?php

namespace Softspring\CmsBundle\EventListener;

use Exception;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ErrorPageListener implements EventSubscriberInterface
{
    protected Environment $twig;
    protected RouterInterface $router;
    protected ?LoggerInterface $logger;

    public function __construct(Environment $twig, RouterInterface $router, ?LoggerInterface $cmsLogger)
    {
        $this->twig = $twig;
        $this->router = $router;
        $this->logger = $cmsLogger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [['onKernelException', -20]], // before Symfony\Component\HttpKernel\EventListener\ErrorListener (-128)
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_sfs_cms_site')) {
            return;
        }

        $site = $request->attributes->get('_sfs_cms_site');
        if (!$site->getConfig()['error_pages']) {
            return;
        }

        $exception = $event->getThrowable();
        $locale = $request->getLocale() ?: $site->getConfig()['default_locale'];

        // fix missing _locale in router context
        $this->router->setContext((new RequestContext())->setParameter('_locale', $locale));

        $errorPage = null;

        if ($exception instanceof NotFoundHttpException) {
            $errorPage = $this->renderErrorTemplate($this->searchErrorTemplate('404', $locale, $site), $locale);
        }

        if ($errorPage) {
            $event->setResponse(new Response($errorPage, 404));
            $event->stopPropagation(); // do not process Symfony exception if site has been configured
        }
    }

    protected function searchErrorTemplate(string $errorCode, string $locale, SiteInterface $site): array
    {
        $errorTemplates = [];

        foreach ($site->getConfig()['error_pages'] as $_errorCode => $templates) {
            if (isset($templates[$locale])) {
                $_errorCode = "$_errorCode";
                if ($errorCode === $_errorCode) {
                    $errorTemplates = array_merge($errorTemplates, $templates[$locale]);
                }

                if (str_ends_with($_errorCode, 'xx') && $errorCode[0] === $_errorCode[0]) {
                    $errorTemplates = array_merge($errorTemplates, $templates[$locale]);
                }
            }
        }

        return $errorTemplates;
    }

    protected function renderErrorTemplate(array $templates, string $locale): ?string
    {
        foreach ($templates as $template) {
            try {
                if (str_ends_with($template, '.twig')) {
                    return $this->twig->render($template, ['locale' => $locale]);
                } else {
                    return file_get_contents($template);
                }
            } catch (Exception $e) {
                // do not throw any exception, try render next template
                $this->logger && $this->logger->error(sprintf('ERROR RENDERING ERROR PAGE (%s): %s', $template, $e->getMessage()));
            }
        }

        return null;
    }
}
