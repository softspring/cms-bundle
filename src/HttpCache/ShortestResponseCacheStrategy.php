<?php

namespace Softspring\CmsBundle\HttpCache;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\ResponseCacheStrategyInterface;

class ShortestResponseCacheStrategy implements ResponseCacheStrategyInterface
{
    private array $ttls = [];
    private bool $isPrivate = false;

    public function add(Response $response): void
    {
        if ($response->headers->hasCacheControlDirective('private')) {
            $this->isPrivate = true;
        }

        if ($response->getTtl()) {
            $this->ttls[] = $response->getTtl();
        }
    }

    public function update(Response $response): void
    {
        if (empty($this->ttls)) {
            return;
        }

        if ($this->isPrivate) {
            $response->setPrivate();
        } else {
            $response->setPublic();
        }

        $minTtl = min($this->ttls);
        $response->setSharedMaxAge($minTtl);

        if ((int) $response->getMaxAge() > (int) $minTtl) {
            $response->setMaxAge($minTtl);
        }
    }
}
