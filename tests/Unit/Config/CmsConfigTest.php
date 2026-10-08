<?php

namespace Softspring\CmsBundle\Test\Unit\Config;

use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\Site;

class CmsConfigTest extends TestCase
{
    public function testMissingConfiguredSiteIsPreservedAsDisabled(): void
    {
        $site = new Site();
        $site->setId('legacy');
        $site->setConfig(['hosts' => [], 'allowed_content_types' => ['page']]);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findAll')->willReturn([$site]);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->method('getRepository')->willReturn($repository);
        $siteManager->expects($this->never())->method('deleteEntity');
        $siteManager->expects($this->once())->method('saveEntity')->with($site);

        $cmsConfig = new CmsConfig([], [], [], [], [], [], $siteManager);

        $this->assertSame(['legacy' => $site], $cmsConfig->getSites());
        $this->assertSame([], $cmsConfig->getSites(true));
        $this->assertFalse($site->isEnabled());
        $this->assertSame(['enabled' => false, 'hosts' => [], 'allowed_content_types' => ['page']], $site->getConfig());
    }

    public function testOnlyEnabledSitesCanBeRequested(): void
    {
        $enabled = new Site();
        $enabled->setId('enabled');
        $enabled->setConfig(['enabled' => true]);

        $disabled = new Site();
        $disabled->setId('disabled');
        $disabled->setConfig(['enabled' => false]);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findAll')->willReturn([$enabled, $disabled]);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->method('getRepository')->willReturn($repository);

        $cmsConfig = new CmsConfig([], [], [], [], [], [
            'enabled' => $enabled->getConfig(),
            'disabled' => $disabled->getConfig(),
        ], $siteManager);

        $this->assertSame(['enabled' => $enabled], $cmsConfig->getSites(true));
        $this->assertSame($disabled, $cmsConfig->getSite('disabled'));
        $this->assertNull($cmsConfig->getSite('disabled', false, true));
    }
}
