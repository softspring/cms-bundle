<?php

namespace Softspring\CmsBundle\HttpCache;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\ResponseCacheStrategyInterface;

class ShortestResponseCacheStrategy implements ResponseCacheStrategyInterface
{
    private array $ttls = [];
    private bool $isPrivate = false;
    private bool $cacheable = true;

    public function add(Response $response): void
    {
        if ($response->headers->hasCacheControlDirective('private')) {
            $this->isPrivate = true;
        }

        if ($response->getTtl()) {
            $this->ttls[] = $response->getTtl();
        } else {
            $this->cacheable = false;
        }
    }

    public function update(Response $response): void
    {
        if ([] === $this->ttls) {
            return;
        }

        if (!$this->cacheable || !$response->isCacheable()) {
            return;
        }

        if ($this->isPrivate) {
            $response->setPrivate();
        } else {
            $response->setPublic();
        }

        $response->getTtl() && $this->ttls[] = $response->getTtl();
        $minTtl = min($this->ttls);
        $response->setSharedMaxAge($minTtl);

        if ((int) $response->getMaxAge() > (int) $minTtl) {
            $response->setMaxAge($minTtl);
        }
    }
}
