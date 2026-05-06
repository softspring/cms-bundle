<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Render;

use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Render\Error\RenderErrorList;
use Softspring\CmsBundle\Render\Exception\RenderException;
use Symfony\Component\HttpFoundation\Request;

interface ContentVersionRendererInterface
{
    /**
     * @throws RenderException
     */
    public function render(ContentVersionInterface $version, Request $request, ?RenderErrorList $renderErrorList = null, ?array $compiledContainers = null): string;

    /**
     * @throws RenderException
     */
    public function renderContainers(ContentVersionInterface $version, Request $request, ?RenderErrorList $renderErrorList = null);
}
