<?php

namespace Softspring\CmsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class BlockController extends AbstractController
{
    use EnableSchedulableContentTrait;

    protected EntityManagerInterface $em;
    protected CmsConfig $cmsConfig;
    protected BlockManagerInterface $blockManager;
    protected bool $debug;
    protected Environment $twig;
    protected ?LoggerInterface $cmsLogger;

    public function __construct(EntityManagerInterface $em, CmsConfig $cmsConfig, BlockManagerInterface $blockManager, bool $debug, Environment $twig, ?LoggerInterface $cmsLogger)
    {
        $this->em = $em;
        $this->cmsConfig = $cmsConfig;
        $this->blockManager = $blockManager;
        $this->debug = $debug;
        $this->twig = $twig;
        $this->cmsLogger = $cmsLogger;
    }

    public function renderByType(string $type, Request $request): Response
    {
        $this->enableSchedulableFilter();

        try {
            $config = $this->cmsConfig->getBlock($type);

            if (!$config['static']) {
                $block = $this->getMoreRestrictiveBlock($this->blockManager->getRepository()->findByType($type));

                if (!$block) {
                    $this->cmsLogger && $this->cmsLogger->notice(sprintf('CMS block %s not found', $type));

                    return new Response();
                }

                $response = new Response($this->twig->render($config['render_template'], $block->getData() + ['_block' => $block]));
            } else {
                $response = new Response($this->twig->render($config['render_template']));
            }

            if (false !== $config['cache_ttl'] && !$request->attributes->has('_cms_preview')) {
                $response->setPublic();
                $response->setMaxAge($config['cache_ttl']);
            }

            return $response;
        } catch (\Exception $e) {
            return $this->renderBlockException("An exception has occurred rendering a block by type '$type'", $e);
        }
    }

    public function renderById(string $id, Request $request): Response
    {
        $this->enableSchedulableFilter();

        try {
            /** @var ?BlockInterface $block */
            $block = $this->blockManager->getRepository()->findOneById($id);

            if (!$block) {
                $this->cmsLogger && $this->cmsLogger->error(sprintf('CMS missing block %s', $id));

                return new Response();
            }

            $type = $block->getType();
            $config = $this->cmsConfig->getBlock($type);

            if (!$config['static']) {
                $response = new Response($this->twig->render($config['render_template'], $block->getData() + ['_block' => $block]));
            } else {
                $response = new Response($this->twig->render($config['render_template']));
            }

            if (false !== $config['cache_ttl'] && !$request->attributes->has('_cms_preview')) {
                $response->setPublic();
                $response->setMaxAge($config['cache_ttl']);
            }

            return $response;
        } catch (\Exception $e) {
            return $this->renderBlockException("An exception has occurred rendering a block with id '$id'", $e);
        }
    }

    protected function renderBlockException(string $message, \Exception $exception): Response
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
            $start = $block->getPublishStartDate() ? $block->getPublishStartDate()->getTimestamp() : 0;
            $end = $block->getPublishEndDate() ? $block->getPublishEndDate()->getTimestamp() : PHP_INT_MAX;

            return (int) ($end - $start);
        };
        usort($blocks, function (BlockInterface $block1, BlockInterface $block2) use ($getPublishedBlockSecondsFn) {
            return $getPublishedBlockSecondsFn($block1) <=> $getPublishedBlockSecondsFn($block2);
        });

        /** @var BlockInterface|false $block */
        $block = current($blocks);

        return $block ?: null;
    }
}
