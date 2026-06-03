<?php

namespace Softspring\CmsBundle\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Render\BlockRenderer;
use Softspring\CmsBundle\Utils\DataMigrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class BlockController extends AbstractController
{
    use EnableSchedulableContentTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected CmsConfig $cmsConfig,
        protected BlockManagerInterface $blockManager,
        protected BlockRenderer $blockRenderer,
        protected bool $debug,
        protected Environment $twig,
        protected string $blockCacheType,
        protected ?LoggerInterface $cmsLogger,
    ) {
    }

    public function renderByType(string $type, Request $request): Response
    {
        try {
            $this->preprocessPreviewRequest($request);
            $this->enableSchedulableFilter();

            $config = $this->cmsConfig->getBlock($type);

            if (isset($config['render_url']) && $config['render_url']) {
                return new Response($this->blockRenderer->renderBlockByType($type, $request->query->all()));
            }

            if (!$config['static']) {
                $block = $this->getMoreRestrictiveBlock($this->blockManager->getRepository()->findByType($type));

                if (!$block instanceof BlockInterface) {
                    $this->cmsLogger && $this->cmsLogger->error(sprintf('CMS missing block %s', $type));

                    return new Response();
                }

                $blockData = DataMigrator::migrate($config['revision_migration_scripts'], $block->getData(), $config['revision'], $this->cmsConfig);
                $response = new Response($this->twig->render($config['render_template'], $blockData + [
                    '_block' => $block,
                    '_block_config' => $config,
                ]));
            } else {
                $response = new Response($this->twig->render($config['render_template'], ['_block_config' => $config]));
            }

            if ('ttl' !== $this->blockCacheType && false !== $config['cache_ttl'] && !$request->attributes->has('_cms_preview')) {
                if ('public' === $config['cache_type']) {
                    $response->setPublic();
                }
                if ('private' === $config['cache_type']) {
                    $response->setPrivate();
                }
                $response->setMaxAge($config['cache_ttl']);
            }

            return $response;
        } catch (Exception $e) {
            return $this->renderBlockException("An exception has occurred rendering a block by type '$type'", $e);
        }
    }

    public function renderById(string $id, Request $request): Response
    {
        try {
            $this->preprocessPreviewRequest($request);
            $this->enableSchedulableFilter();

            /** @var ?BlockInterface $block */
            $block = $this->blockManager->getRepository()->findOneById($id);

            if (!$block) {
                $this->cmsLogger && $this->cmsLogger->error(sprintf('CMS missing block %s', $id));

                return new Response();
            }

            $type = $block->getType();
            $config = $this->cmsConfig->getBlock($type);

            if (!$config['static']) {
                $response = new Response($this->twig->render($config['render_template'], $block->getData() + [
                    '_block' => $block,
                    '_block_config' => $config,
                ]));
            } else {
                $response = new Response($this->twig->render($config['render_template'], ['_block_config' => $config]));
            }

            if ('ttl' !== $this->blockCacheType && false !== $config['cache_ttl'] && !$request->attributes->has('_cms_preview')) {
                $response->setPublic();
                $response->setMaxAge($config['cache_ttl']);
            }

            return $response;
        } catch (Exception $e) {
            return $this->renderBlockException("An exception has occurred rendering a block with id '$id'", $e);
        }
    }

    protected function renderBlockException(string $message, Exception $exception): Response
    {
        $this->cmsLogger && $this->cmsLogger->critical(sprintf('%s: %s', $message, $exception->getMessage()));

        if (!$this->debug) {
            return new Response('<!-- error rendering block, see logs -->');
        }

        $trace = nl2br($exception->getTraceAsString());
        $error = <<<ERROR
<section class="border border-danger p-4 text-white bg-danger">
<h4>$message</h4>
<p>{$exception->getMessage()}</p>
<p>$trace</p>
</section>
ERROR;

        return new Response($error);
    }

    /**
     * @param BlockInterface[] $blocks
     */
    protected function getMoreRestrictiveBlock(array $blocks): ?BlockInterface
    {
        // find more restrictive block
        $getPublishedBlockSecondsFn = function (BlockInterface $block): int {
            $start = $block->getPublishStartDate() instanceof DateTime ? $block->getPublishStartDate()->getTimestamp() : 0;
            $end = $block->getPublishEndDate() instanceof DateTime ? $block->getPublishEndDate()->getTimestamp() : PHP_INT_MAX;

            return (int) ($end - $start);
        };
        usort($blocks, function (BlockInterface $block1, BlockInterface $block2) use ($getPublishedBlockSecondsFn): int {
            return $getPublishedBlockSecondsFn($block1) <=> $getPublishedBlockSecondsFn($block2);
        });

        /** @var BlockInterface|false $block */
        $block = current($blocks);

        return $block ?: null;
    }

    protected function preprocessPreviewRequest(Request $request): void
    {
        $site = $this->getRequestValue($request, '_sfs_cms_site');
        $siteId = $this->getRequestValue($request, '_site');
        $locale = $this->getRequestValue($request, '_locale');

        if (!$request->attributes->has('_sfs_cms_site') && $site) {
            $request->attributes->set('_sfs_cms_site', $this->cmsConfig->getSite($site));
        }
        if (!$request->attributes->has('_sfs_cms_site') && $siteId) {
            $request->attributes->set('_sfs_cms_site', $this->cmsConfig->getSite($siteId));
        }
        if (!$request->attributes->has('_locale') && $locale) {
            $request->attributes->set('_locale', $locale);
            $request->setLocale($locale);
        }
    }

    protected function getRequestValue(Request $request, string $key): mixed
    {
        if ($request->attributes->has($key)) {
            return $request->attributes->get($key);
        }

        if ($request->query->has($key)) {
            return $request->query->get($key);
        }

        return $request->request->get($key);
    }
}
