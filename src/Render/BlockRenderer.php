<?php

namespace Softspring\CmsBundle\Render;

use Exception;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidBlockException;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\RouterInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

class BlockRenderer extends AbstractRenderer
{
    protected bool $profilerEnabled;
    protected bool $esiEnabled;
    protected array $profilerDebugCollectorData = [];

    public function __construct(
        RequestStack $requestStack,
        protected CmsConfig $cmsConfig,
        protected Environment $twig,
        protected RouterInterface $router,
        ?EntrypointLookupInterface $entrypointLookup,
        ?Profiler $profiler,
        ?Esi $esi
    ) {
        parent::__construct($requestStack, $entrypointLookup, $router);
        $this->profilerEnabled = (bool) $profiler;
        $this->esiEnabled = (bool) $esi;
    }

    protected function paramsAsString(array $params): string
    {
        $params = array_map(function ($k, $v) {
            if (is_bool($v)) {
                return "'$k':".($v ? 'true' : 'false');
            }

            return is_string($v) ? "'$k':'$v'" : "'$k':$v";
        }, array_keys($params), array_values($params));

        return implode(',', $params);
    }

    /**
     * @throws RenderException
     * @throws InvalidBlockException
     */
    public function renderBlockByType(string $type, array $params = [], ?string $locale = null, mixed $site = null): string
    {
        $blockConfig = $this->cmsConfig->getBlock($type);

        if ($blockConfig['esi'] && !$this->isPreview()) {
            if (!$this->esiEnabled) {
                throw new Exception('You must enable esi with framework.esi configuration to use it in CMS');
            }

            $renderFunction = 'render_esi';
            $params['ignore_errors'] = true;
        } else {
            $renderFunction = 'render';
        }

        $params['_locale'] = $locale ?? $this->requestStack->getCurrentRequest()?->getLocale();
        $site && $params['_site'] = $site;
        if (!empty($blockConfig['render_url'])) {
            $params_string = $this->paramsAsString($params);
            $twigCode = "{{ $renderFunction(url('{$blockConfig['render_url']}', {{$params_string}})) }}";
        } else {
            // $twigCode = "{{ $renderFunction(url('sfs_cms_block_render_by_type', {'type':'$type'})) }}";
            $params['type'] = $type;
            $params_string = $this->paramsAsString($params);
            $controller = "controller('Softspring\\\\CmsBundle\\\\Controller\\\\BlockController::renderByType', {{$params_string}})";

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

        return $this->encapsulateEsiCapableRender(function (Request $request) use ($template, $locale) {
            $locale && $request->setLocale($locale);

            return $template->render();
        });
    }

    public function renderBlock(BlockInterface $block, ?string $locale = null, bool $forceEsiRender = false): string
    {
        $type = $block->getType();
        $blockId = $block->getId();

        $blockConfig = $this->cmsConfig->getBlock($type);

        if ($blockConfig['esi'] && !$this->isPreview()) {
            if (!$this->esiEnabled) {
                throw new Exception('You must enable esi with framework.esi configuration to use it in CMS');
            }

            $renderFunction = $forceEsiRender ? 'render' : 'render_esi';
        } else {
            $renderFunction = 'render';
        }

        if (!empty($blockConfig['render_url'])) {
            $localeParam = $locale ? ", {'_locale':'$locale'}" : '';
            $twigCode = "{{ $renderFunction(url('{$blockConfig['render_url']}'$localeParam)) }}";
        } else {
            // $twigCode = "{{ $renderFunction(url('sfs_cms_block_render_by_type', {'type':'$type'})) }}";
            $localeParam = $locale ? ", '_locale':'$locale'" : '';
            $twigCode = "{{ $renderFunction(controller('Softspring\\\\CmsBundle\\\\Controller\\\\BlockController::renderById', {'id':'$blockId'$localeParam})) }}";
        }

        $template = twig_template_from_string($this->twig, $twigCode);

        if ($this->profilerEnabled) {
            $this->profilerDebugCollectorData[] = [
                'type' => $type,
                'blockId' => $blockId,
                'config' => $blockConfig,
            ];
        }

        return $this->encapsulateEsiCapableRender(function (Request $request) use ($template, $locale) {
            $locale && $request->setLocale($locale);

            return $template->render();
        });
    }

    public function getDebugCollectorData(): array
    {
        return $this->profilerDebugCollectorData;
    }
}
