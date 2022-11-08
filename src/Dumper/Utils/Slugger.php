<?php

namespace Softspring\CmsBundle\Dumper\Utils;

use Symfony\Component\String\Slugger\AsciiSlugger;

class Slugger
{
    public static function lowerSlug(string $string): string
    {
        return self::slug(strtolower($string));
    }

    public static function slug(string $string): string
    {
        $slugger = new AsciiSlugger();

        return $slugger->slug($string);
    }
}
