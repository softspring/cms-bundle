<?php

namespace Softspring\CmsBundle\Render;

use Softspring\CmsBundle\Model\BlockInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RenderBlock
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var array
     */
    protected $blockTypes;

    /**
     * RenderBlock constructor.
     *
     * @param Environment $twig
     * @param array       $blockTypes
     */
    public function __construct(Environment $twig, array $blockTypes)
    {
        $this->twig = $twig;
        $this->blockTypes = $blockTypes;
    }

    /**
     * @param BlockInterface $block
     *
     * @return string
     */
    public function render(BlockInterface $block): string
    {
        if (!isset($this->blockTypes[$block->getKey()])) {
            // TODO LOG ERROR
            return '';
        }

        $template = $this->blockTypes[$block->getKey()]['render_template'];

        try {
            return $this->twig->render($template, $block->getContent());
        } catch (LoaderError $e) {
            // TODO LOG ERROR
            return '';
        } catch (RuntimeError $e) {
            // TODO LOG ERROR
            return '';
        } catch (SyntaxError $e) {
            // TODO LOG ERROR
            return '';
        }
    }
}