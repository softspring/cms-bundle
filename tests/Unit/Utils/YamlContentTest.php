<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Utils;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Utils\YamlContent;

class YamlContentTest extends TestCase
{
    public function testItDumpsAndSavesYamlContent(): void
    {
        $yaml = YamlContent::yaml(['page' => ['title' => 'Home']]);

        self::assertStringContainsString('page:', $yaml);
        self::assertStringContainsString('title: Home', $yaml);

        $file = tempnam(sys_get_temp_dir(), 'cms_yaml_');

        try {
            self::assertSame($file, YamlContent::save(['enabled' => true], $file));
            self::assertStringContainsString('enabled: true', file_get_contents($file));
        } finally {
            if (is_string($file) && file_exists($file)) {
                unlink($file);
            }
        }
    }
}
