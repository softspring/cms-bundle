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

    // canonicalContent became translatable in revision 3.
    // Migrate previous scalar/entity value to a translatable structure.
    if ($originVersion < 3 && $targetVersion >= 3 && isset($seo['canonicalContent']) && !is_array($seo['canonicalContent'])) {
        $seo['canonicalContent'] = \Softspring\TranslatableBundle\Model\Translation::createFromArray([
            'en' => $seo['canonicalContent'],
            '_default' => 'en',
        ]);
    }

    return $seo;
};
