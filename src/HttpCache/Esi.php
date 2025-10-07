<?php

namespace Softspring\CmsBundle\HttpCache;

use Symfony\Component\HttpKernel\HttpCache\Esi as BaseEsi;
use Symfony\Component\HttpKernel\HttpCache\ResponseCacheStrategy;
use Symfony\Component\HttpKernel\HttpCache\ResponseCacheStrategyInterface;

class Esi extends BaseEsi
{
    public function __construct(array $contentTypes = ['text/html', 'text/xml', 'application/xhtml+xml', 'application/xml'], protected string $strategyClass = ResponseCacheStrategy::class)
    {
        parent::__construct($contentTypes);
    }

    public function createCacheStrategy(): ResponseCacheStrategyInterface
    {
        return new $this->strategyClass();
    }
}
