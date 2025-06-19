<?php

namespace Softspring\CmsBundle\Test\Unit\Render\Request;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Model\Site;
use Softspring\CmsBundle\Render\Isolated\IsolatedRequest;

class IsolatedRequestTest extends TestCase
{
    public function testIsolatedRequest()
    {
        $isolatedRequest = IsolatedRequest::createIsolated('fr', $this->createMock(Site::class), false);

        $this->assertInstanceOf(IsolatedRequest::class, $isolatedRequest);
        $this->assertEquals('fr', $isolatedRequest->getLocale());
        $this->assertEquals('https', $isolatedRequest->getScheme());
        $this->assertEquals(443, $isolatedRequest->getPort());

        $isolatedRequest->attributes->set('testAttribute', 'testValue');
        $this->assertEquals('testValue', $isolatedRequest->attributes->get('testAttribute'));

        $isolatedRequest->query->set('testQuery', 'queryValue');
        $this->assertEquals('queryValue', $isolatedRequest->query->get('testQuery'));

        $isolatedRequest->request->set('testRequest', 'requestValue');
        $this->assertEquals('requestValue', $isolatedRequest->request->get('testRequest'));

        $isolatedRequest->cookies->set('testCookie', 'cookieValue');
        $this->assertEquals('cookieValue', $isolatedRequest->cookies->get('testCookie'));

        $isolatedRequest->headers->set('HTTP_TEST_HEADER', 'headerValue');
        $this->assertEquals('headerValue', $isolatedRequest->headers->get('HTTP_TEST_HEADER'));

        $isolatedRequest->server->set('HTTP_HOST', 'example.com');
        $this->assertEquals('example.com', $isolatedRequest->server->get('HTTP_HOST'));
    }
}
