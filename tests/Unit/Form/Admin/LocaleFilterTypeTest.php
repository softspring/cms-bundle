<?php

namespace Softspring\CmsBundle\Test\Unit\Form\Admin;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Form\Admin\LocaleFilterType;
use Symfony\Component\Form\Event\PreSetDataEvent;

class LocaleFilterTypeTest extends TestCase
{
    /**
     * @dataProvider migratingFormatProvider
     */
    public function testMigratingFormat(string $description, array $provided, array $expected, array $available_locales): void
    {
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $event = new PreSetDataEvent($form, $provided);

        LocaleFilterType::onPreSetData($event, $available_locales);

        $this->assertEquals($expected, $event->getData(), $description);
    }

    public static function migratingFormatProvider(): array
    {
        return [
            [
                'legacy format empty',
                [],
                [
                    'en' => false,
                    'fr' => false,
                    'de' => false
                ],
                ['en', 'fr', 'de']
            ],
            [
                'legacy format some selected',
                [ 0 => 'en', 1 => 'fr' ],
                [
                    'en' => true,
                    'fr' => true,
                    'de' => false
                ],
                ['en', 'fr', 'de']
            ],
            [
                'legacy format all selected',
                [ 0 => 'en',  1 => 'de', 2 => 'fr'],
                [
                    'en' => true,
                    'fr' => true,
                    'de' => true
                ],
                ['en', 'fr', 'de']
            ],
            [
                'new format some with values, so others are new locales',
                ['de' => false],
                [
                    'en' => true,
                    'fr' => true,
                    'de' => false
                ],
                ['en', 'fr', 'de']
            ],
            [
                'new format all with values',
                [
                    'en' => true,
                    'fr' => false,
                    'de' => true
                ],
                [
                    'en' => true,
                    'fr' => false,
                    'de' => true
                ],
                ['en', 'fr', 'de']
            ],
            [
                'new format all with values',
                [
                    'en' => false,
                    'fr' => false,
                    'de' => false
                ],
                [
                    'en' => false,
                    'fr' => false,
                    'de' => false
                ],
                ['en', 'fr', 'de']
            ],
        ];
    }
}