<?php

return static function (array $seo, int $originVersion, int $targetVersion): array {
    if ($originVersion < 2 && $targetVersion >= 2) {
        if (is_array($seo['metaTitle'])) {
            $seo['metaTitle'] = \Softspring\TranslatableBundle\Model\Translation::createFromArray($seo['metaTitle']);
        }
        if (is_array($seo['metaDescription'])) {
            $seo['metaDescription'] = \Softspring\TranslatableBundle\Model\Translation::createFromArray($seo['metaDescription']);
        }
        if (is_array($seo['metaKeywords'])) {
            $seo['metaKeywords'] = \Softspring\TranslatableBundle\Model\Translation::createFromArray($seo['metaKeywords']);
        }
    }

    return $seo;
};
