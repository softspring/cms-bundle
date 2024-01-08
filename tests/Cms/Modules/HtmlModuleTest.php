<?php

namespace Softspring\CmsBundle\Test\Cms\Modules;

use Softspring\CmsBundle\Tests\ModuleTestCase;

class HtmlModuleTest extends ModuleTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->moduleName = 'html';
        $this->modulePath = realpath(__DIR__ . '/../../../cms/modules/html');
    }

    public static function provideModuleRender(): array
    {
        return [
            [
                'data' => [
                    '_module' => 'html',
                    '_revision' => 1,
                    'id' => null,
                    'class' => null,
                    'code' => '<h1>Test</h1>',
                ],
                'expected' => function (string $result) {
                    ModuleTestCase::assertRenderText('Test', $result, 'h1');
                },
            ],
            [
                'data' => [
                    '_module' => 'html',
                    '_revision' => 1,
                    'id' => 'test',
                    'class' => null,
                    'code' => '<h1>Test</h1>',
                ],
                'expected' => function (string $result) {
                    ModuleTestCase::assertRenderText('Test', $result, 'div#test > h1');
                },
            ],
            [
                'data' => [
                    '_module' => 'html',
                    '_revision' => 1,
                    'id' => null,
                    'class' => 'test-class',
                    'code' => '<h1>Test</h1>',
                ],
                'expected' => function (string $result) {
                    ModuleTestCase::assertRenderText('Test', $result, 'div.test-class > h1');
                },
            ],
            [
                'data' => [
                    '_module' => 'html',
                    '_revision' => 1,
                    'id' => 'test',
                    'class' => 'test-class',
                    'code' => '<h1>Test</h1>',
                ],
                'expected' => function (string $result) {
                    ModuleTestCase::assertRenderText('Test', $result, 'div#test.test-class > h1');
                },
            ],
        ];
    }
}
