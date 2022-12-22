<?php

namespace Softspring\CmsBundle\Utils;

class Hash
{
    public static function generate(int $length = 8): string
    {
        return substr(sha1(''.rand(0, 10000000000)), rand(0, 10), $length);
    }
}
