<?php

namespace Softspring\CmsBundle\Render\Isolated;

use Exception;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Softspring\CmsBundle\Render\Module\ModuleRendererFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

class IsolatedRunner
{
    public function __construct(
        protected RequestStack $requestStack,
        protected RouterInterface $router,
        protected Environment $twig,
        protected ModuleRendererFactory $moduleRendererFactory,
        protected ?EntrypointLookupInterface $entrypointLookup,
    ) {
    }

    /**
     * @throws RenderException
     */
    public function isolateEsiCapableRequestRender(callable $renderFunction): mixed
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        // Store the original Surrogate-Capability header if it exists
        if ($currentRequest->headers->has('Surrogate-Capability')) {
            $originalSurrogateCapability = $currentRequest->headers->get('Surrogate-Capability');
        }

        // Set the Surrogate-Capability header to indicate ESI support
        $currentRequest->headers->set('Surrogate-Capability', 'ESI/1.0');

        // if isolate_request attribute is not set, set it to true
        if (!$currentRequest->attributes->has('isolate_request')) {
            $currentRequest->attributes->set('isolate_request', true);
        }

        // if isolate_request query parameter is set, override the attribute
        if ($currentRequest->query->has('isolate_request')) {
            $currentRequest->attributes->set('isolate_request', filter_var($currentRequest->query->get('isolate_request'), FILTER_VALIDATE_BOOLEAN));
        }

        // do the render
        $result = $this->isolateRequestRender($currentRequest, $renderFunction);

        // Restore the original Surrogate-Capability header if it was set
        isset($originalSurrogateCapability) ?
            $currentRequest->headers->set('Surrogate-Capability', $originalSurrogateCapability) :
            $currentRequest->headers->remove('Surrogate-Capability');

        return $result;
    }

    /**
     * @throws RenderException
     */
    public function isolateRequestRender(IsolatedRequest|Request $request, callable $renderFunction): mixed
    {
        // reset webpack encore entrypoint lookup to avoid cache issues
        $this->entrypointLookup && $this->entrypointLookup->reset();

        // inject the current request into the request stack
        // this is necessary to ensure that the request is available
        // and will be removed after rendering
        $this->requestStack->push($request);

        // create a new isolated app variable to avoid using the original one
        $oldAppVariable = $this->twig->getGlobals()['app'] ?? null;
        $this->twig->addGlobal('app', new IsolatedAppVariable($oldAppVariable));

        // if the request has a site attribute, we need to set the router context
        if ($request->attributes->has('_sfs_cms_site')) {
            // save the previous request context to restore it later
            $prevRequestContext = $this->router->getContext();

            // create a new request context based on the site's canonical host and scheme
            $newRequestContext = new RequestContext();
            $site = $request->attributes->get('_sfs_cms_site');
            $newRequestContext->setHost($site->getCanonicalHost());
            $newRequestContext->setScheme($site->getCanonicalScheme());
            $newRequestContext->setBaseUrl('');
            $this->router->setContext($newRequestContext);
            $request->attributes->set('_locale', $request->getLocale());
        }

        try {
            $moduleRenderer = $this->moduleRendererFactory->create($this->twig);
            $result = $renderFunction($request, $this->twig, $moduleRenderer);
        } catch (Exception $e) {
            if ($e instanceof RenderException) {
                throw $e;
            }
            throw new RenderException('Error rendering request', 0, $e);
        } finally {
            // restore the previous request context if it was set
            isset($prevRequestContext) && $this->router->setContext($prevRequestContext);

            // remove the current request from the request stack
            $this->requestStack->pop();

            // restore the old app variable
            $this->twig->addGlobal('app', $oldAppVariable);
        }

        return $result;
    }
}
