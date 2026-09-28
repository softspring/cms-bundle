<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Helper\LocaleHelper;
use Softspring\CmsBundle\Translator\TranslatableContext;

class LocaleHelperTest extends TestCase
{
    public function testReturnsContextLocales(): void
    {
        $helper = new LocaleHelper($this->createMock(CmsConfig::class), new TranslatableContext(['es', 'en'], 'es'));

        self::assertSame(['es', 'en'], $helper->getEnabledLocales());
        self::assertSame('es', $helper->getDefaultLocale());
    }

    public function testNormalizesFormAvailableLocalesFromExplicitValue(): void
    {
        $helper = new LocaleHelper($this->createMock(CmsConfig::class), new TranslatableContext(['en'], 'en'));

        self::assertSame(['en', 'es'], $helper->normalizeFormAvailableLocalesForSites(['es', 'en', 'es'], null));
    }

    public function testNormalizesFormAvailableLocalesFromSites(): void
    {
        $firstSite = new Site();
        $firstSite->setConfig(['locales' => ['es', 'en']]);

        $secondSite = new Site();
        $secondSite->setConfig(['locales' => ['fr', 'en']]);

        $helper = new LocaleHelper($this->createMock(CmsConfig::class), new TranslatableContext(['de'], 'de'));

        self::assertSame(['en', 'es', 'fr'], $helper->normalizeFormAvailableLocalesForSites(null, [$firstSite, $secondSite]));
    }

    public function testNormalizesFormAvailableLocalesFromContextWhenSitesAreEmpty(): void
    {
        $helper = new LocaleHelper($this->createMock(CmsConfig::class), new TranslatableContext(['es', 'en'], 'es'));

        self::assertSame(['en', 'es'], $helper->normalizeFormAvailableLocalesForSites(null, []));
    }

    public function testNormalizesFormAvailableLocalesForContent(): void
    {
        $page = new Page();
        $page->setLocales(['es', 'en', 'es']);

        $helper = new LocaleHelper($this->createMock(CmsConfig::class), new TranslatableContext(['fr'], 'fr'));

        self::assertSame(['en', 'es'], $helper->normalizeFormAvailableLocalesForContent(null, $page));
        self::assertSame(['de', 'fr'], $helper->normalizeFormAvailableLocalesForContent(['fr', 'de', 'fr'], $page));
    }
}
