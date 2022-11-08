<?php

namespace Softspring\CmsBundle\Dumper\Utils;

use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class Yaml
{
    public static function yaml(array $data): string
    {
        return SymfonyYaml::dump($data, 100, 4,
            SymfonyYaml::DUMP_OBJECT_AS_MAP
            & SymfonyYaml::DUMP_NULL_AS_TILDE
            & SymfonyYaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE
            & SymfonyYaml::DUMP_OBJECT
            & SymfonyYaml::DUMP_EXCEPTION_ON_INVALID_TYPE
            & SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );
    }

    public static function save(array $data, string $filePath): ?string
    {
        file_put_contents($filePath, self::yaml($data));

        return $filePath;
    }
}
