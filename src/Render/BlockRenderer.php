<?php

namespace Softspring\CmsBundle\Render;

use Exception;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidBlockException;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Softspring\CmsBundle\Render\Isolated\IsolatedRunner;
use Softspring\CmsBundle\Utils\Parser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\RouterInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

class BlockRenderer
{
    protected bool $profilerEnabled;
    protected bool $esiEnabled;
    protected array $profilerDebugCollectorData = [];

    public function __construct(
        protected RequestStack $requestStack,
        protected CmsConfig $cmsConfig,
        protected Environment $twig,
        protected RouterInterface $router,
        protected IsolatedRunner $isolatedRunner,
        protected ?EntrypointLookupInterface $entrypointLookup,
        protected ?Profiler $profiler,
        protected ?Esi $esi,
    ) {
        $this->profilerEnabled = (bool) $profiler;
        $this->esiEnabled = (bool) $esi;
    }

    /**
     * @throws RenderException
     * @throws InvalidBlockException
     */
    public function renderBlockByType(string $type, array $params = [], ?string $locale = null, mixed $site = null): string
    {
        $blockConfig = $this->cmsConfig->getBlock($type);

        if ($blockConfig['esi']) {
            if (!$this->esiEnabled) {
                throw new Exception('You must enable esi with framework.esi configuration to use it in CMS');
            }

            $renderFunction = 'render_esi';
            $params['ignore_errors'] = true;
            $params['isolate_request'] = !is_bool($blockConfig['isolate_request']) || $blockConfig['isolate_request'];
        } else {
            $renderFunction = 'render';
        }

        $params['_locale'] = $locale ?? $request?->getLocale() ?? $this->requestStack->getCurrentRequest()?->getLocale();
        $params['_site'] = $site ?? $request?->attributes->get('_site') ?? $this->requestStack->getCurrentRequest()?->attributes->get('_site');
        if (!empty($blockConfig['render_url'])) {
            $params_string = '{'.Parser::arrayToParamsString($params).'}';
            $twigCode = "{{ $renderFunction(url('{$blockConfig['render_url']}', $params_string)) }}";
        } else {
            // $twigCode = "{{ $renderFunction(url('sfs_cms_block_render_by_type', {'type':'$type'})) }}";
            $params['type'] = $type;
            $params_string = '{'.Parser::arrayToParamsString($params).'}';
            $controller = "controller('Softspring\\\\CmsBundle\\\\Controller\\\\BlockController::renderByType', $params_string)";

            if ('render_esi' == $renderFunction) {
                // {{ fragment_uri(controller, absolute = false, strict = true, sign = true) }}
                $twigCode = "{{ $renderFunction(fragment_uri($controller, true, true, true)) }}";
            } else {
                $twigCode = "{{ $renderFunction($controller) }}";
            }
        }

        $template = twig_template_from_string($this->twig, $twigCode);

        if ($this->profilerEnabled) {
            $this->profilerDebugCollectorData[] = [
                'type' => $type,
                'config' => $blockConfig,
            ];
        }

        return $this->isolatedRunner->isolateEsiCapableRequestRender(function (Request $request) use ($template, $locale) {
            $locale && $request->setLocale($locale);

            return $template->render();
        });
    }

    /**
     * @throws RenderException
     * @throws InvalidBlockException
     */
    public function renderBlock(BlockInterface $block, ?string $locale = null, bool $forceEsiRender = false): string
    {
        $type = $block->getType();
        $blockId = $block->getId();

        $blockConfig = $this->cmsConfig->getBlock($type);

        $params = [];
        $params['_locale'] = $locale;
        $params['isolate_request'] = !is_bool($blockConfig['isolate_request']) || $blockConfig['isolate_request'];

        if ($blockConfig['esi']) {
            if (!$this->esiEnabled) {
                throw new Exception('You must enable esi with framework.esi configuration to use it in CMS');
            }

            $renderFunction = $forceEsiRender ? 'render' : 'render_esi';
        } else {
            $renderFunction = 'render';
        }

        if (!empty($blockConfig['render_url'])) {
            $params_string = '{'.Parser::arrayToParamsString($params).'}';
            $twigCode = "{{ $renderFunction(url('{$blockConfig['render_url']}', $params_string)) }}";
        } else {
            // $twigCode = "{{ $renderFunction(url('sfs_cms_block_render_by_type', {'type':'$type'})) }}";
            $params['id'] = $blockId;
            $params_string = '{'.Parser::arrayToParamsString($params).'}';
            $twigCode = "{{ $renderFunction(controller('Softspring\\\\CmsBundle\\\\Controller\\\\BlockController::renderById', $params_string)) }}";
        }

        $template = twig_template_from_string($this->twig, $twigCode);

        if ($this->profilerEnabled) {
            $this->profilerDebugCollectorData[] = [
                'type' => $type,
                'blockId' => $blockId,
                'config' => $blockConfig,
            ];
        }

        return $this->isolatedRunner->isolateEsiCapableRequestRender(function (Request $request) use ($template, $locale) {
            $locale && $request->setLocale($locale);

            return $template->render();
        });
    }

    public function getDebugCollectorData(): array
    {
        return $this->profilerDebugCollectorData;
    }
}
