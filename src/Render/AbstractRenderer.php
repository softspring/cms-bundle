<?php

namespace Softspring\CmsBundle\Render;

use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractRenderer
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    protected function isPreview(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->attributes->has('_cms_preview');
    }
}
