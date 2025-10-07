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
        if (!$response->isCacheable()) {
            $this->isPrivate = true;
        }

        $this->ttls[] = $response->getTtl();
    }

    public function update(Response $response): void
    {
        if ($this->isPrivate) {
            $response->setPrivate();

            return;
        }

        $ttls = array_filter($this->ttls, fn ($ttl) => $ttl > 0);

        if (!empty($ttls)) {
            $minTtl = min($ttls);
            $response->setSharedMaxAge($minTtl);

            if ((int) $response->getMaxAge() > (int) $minTtl) {
                $response->setMaxAge($minTtl);
            }
        }
    }
}
