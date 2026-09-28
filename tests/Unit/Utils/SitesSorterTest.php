<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Utils;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Utils\SitesSorter;

class SitesSorterTest extends TestCase
{
    public function testItSortsSitesByConfiguredOrder(): void
    {
        $first = $this->createSite(['extra' => ['order' => 10]]);
        $default = $this->createSite([]);
        $last = $this->createSite(['extra' => ['order' => 900]]);

        $sorted = SitesSorter::sort([$last, $default, $first]);

        self::assertInstanceOf(ArrayCollection::class, $sorted);
        self::assertSame([$first, $default, $last], $sorted->toArray());
    }

    public function testItAcceptsDoctrineCollections(): void
    {
        $first = $this->createSite(['extra' => ['order' => 1]]);
        $second = $this->createSite(['extra' => ['order' => 2]]);

        self::assertSame([$first, $second], SitesSorter::sort(new ArrayCollection([$second, $first]))->toArray());
    }

    private function createSite(array $config): SiteInterface
    {
        $site = $this->createMock(SiteInterface::class);
        $site->method('getConfig')->willReturn($config);

        return $site;
    }
}
