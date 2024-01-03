<?php

namespace Softspring\CmsBundle\Test\Cms\Modules;

use Softspring\CmsBundle\Tests\ModuleTestCase;

class CssModuleTest extends ModuleTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->moduleName = 'css';
        $this->modulePath = realpath(__DIR__.'/../../../cms/modules/css');
    }

    public static function provideModuleRender(): array
    {
        return [
            [
                'data' => [
                    '_module' => 'css',
                    '_revision' => 1,
                    'css' => 'body { background-color: red; }',
                ],
                'expected' => function($result) {
                    ModuleTestCase::assertRenderText('body { background-color: red; }', $result, null, '//style');
                },
            ]
        ];
    }
}
