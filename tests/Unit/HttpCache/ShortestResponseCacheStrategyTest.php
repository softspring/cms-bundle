<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\HttpCache;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\HttpCache\ShortestResponseCacheStrategy;
use Symfony\Component\HttpFoundation\Response;

class ShortestResponseCacheStrategyTest extends TestCase
{
    public function testItAppliesTheShortestSharedMaxAgeToTheMainResponse(): void
    {
        $strategy = new ShortestResponseCacheStrategy();
        $strategy->add($this->cacheableResponse(120));
        $strategy->add($this->cacheableResponse(30));

        $response = $this->cacheableResponse(90);
        $strategy->update($response);

        self::assertTrue($response->headers->hasCacheControlDirective('public'));
        self::assertSame(30, $response->getTtl());
        self::assertSame(30, $response->getMaxAge());
    }

    public function testPrivateFragmentsMakeTheMainResponsePrivate(): void
    {
        $strategy = new ShortestResponseCacheStrategy();
        $privateFragment = new Response('private content');
        $privateFragment->setPrivate();
        $privateFragment->setMaxAge(45);
        $strategy->add($privateFragment);

        $response = $this->cacheableResponse(120);
        $strategy->update($response);

        self::assertTrue($response->headers->hasCacheControlDirective('public'));
        self::assertSame(45, $response->getTtl());
    }

    public function testItDoesNotUpdateWhenAFragmentIsNotCacheable(): void
    {
        $strategy = new ShortestResponseCacheStrategy();
        $strategy->add(new Response('uncacheable'));
        $strategy->add($this->cacheableResponse(20));

        $response = $this->cacheableResponse(60);
        $strategy->update($response);

        self::assertSame(60, $response->getTtl());
    }

    public function testItDoesNotUpdateWhenNoTtlWasCollected(): void
    {
        $strategy = new ShortestResponseCacheStrategy();

        $response = $this->cacheableResponse(60);
        $strategy->update($response);

        self::assertSame(60, $response->getTtl());
    }

    private function cacheableResponse(int $ttl): Response
    {
        $response = new Response('content');
        $response->setPublic();
        $response->setSharedMaxAge($ttl);
        $response->setMaxAge($ttl);

        return $response;
    }
}
