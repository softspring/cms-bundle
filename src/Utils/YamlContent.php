<?php

namespace Softspring\CmsBundle\Utils;

use Symfony\Component\Yaml\Yaml;

class YamlContent
{
    public static function yaml(array $data): string
    {
        return Yaml::dump($data, 100, 4,
            Yaml::DUMP_OBJECT_AS_MAP
            & Yaml::DUMP_NULL_AS_TILDE
            & Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE
            & Yaml::DUMP_OBJECT
            & Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE
            & Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );
    }

    public static function save(array $data, string $filePath): ?string
    {
        file_put_contents($filePath, self::yaml($data));

        return $filePath;
    }
}
