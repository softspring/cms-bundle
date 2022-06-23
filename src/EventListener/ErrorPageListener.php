<?php

namespace Softspring\CmsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class ErrorPageListener implements EventSubscriberInterface
{
    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
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

        $siteConfig = $request->attributes->get('_sfs_cms_site');
        if (!$siteConfig['error_pages']) {
            return;
        }

        $exception = $event->getThrowable();
        $locale = $request->getLocale() ?: $siteConfig['default_locale'];

        $errorPage = null;

        if ($exception instanceof NotFoundHttpException) {
            $errorPage = $this->renderErrorTemplate($this->searchErrorTemplate('404', $locale, $siteConfig), $locale);
        }

        if ($errorPage) {
            $event->setResponse(new Response($errorPage, 404));
            $event->stopPropagation(); // do not process Symfony exception if site has been configured
        }
    }

    protected function searchErrorTemplate(string $errorCode, string $locale, $siteConfig): array
    {
        $errorTemplates = [];

        foreach ($siteConfig['error_pages'] as $_errorCode => $templates) {
            $_errorCode = "$_errorCode";
            if ($errorCode === $_errorCode) {
                $errorTemplates = array_merge($errorTemplates, $templates[$locale]);
            }

            if (str_ends_with($_errorCode, 'xx') && $errorCode[0] === $_errorCode[0]) {
                $errorTemplates = array_merge($errorTemplates, $templates[$locale]);
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
            } catch (\Exception $e) {
                // do not throw any exception, try render next template
                // maybe log something
                $m = $e->getMessage();
            }
        }

        return null;
    }
}