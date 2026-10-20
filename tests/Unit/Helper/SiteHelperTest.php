<?php

namespace Softspring\CmsBundle\Test\Unit\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Helper\SiteHelper;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\Site;

class SiteHelperTest extends TestCase
{
    public function testContentKeepsDisabledAssignedSitesAlongsideEnabledChoices(): void
    {
        $enabled = new Site();
        $enabled->setId('enabled');
        $enabled->setConfig(['enabled' => true]);

        $disabled = new Site();
        $disabled->setId('disabled');
        $disabled->setConfig(['enabled' => false]);

        $content = $this->createMock(ContentInterface::class);
        $content->method('getSites')->willReturn(new ArrayCollection([$disabled]));

        $cmsConfig = $this->createMock(CmsConfig::class);
        $cmsConfig->method('getContent')->with($content)->willReturn(['_id' => 'page']);
        $cmsConfig->method('getSitesForContent')->with('page', true)->willReturn(['enabled' => $enabled]);

        $availableSites = (new SiteHelper($cmsConfig))->normalizeFormAvailableSites(null, $content);

        $this->assertCount(2, $availableSites);
        $this->assertContains($enabled, $availableSites);
        $this->assertContains($disabled, $availableSites);
    }
}
