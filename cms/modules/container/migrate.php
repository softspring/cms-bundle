<?php

return static function (array $data, int $originVersion, int $targetVersion): array {
    if ($originVersion < 2 && $targetVersion >= 2) {
        /*
         * New fields
         */
        $data['tag_type'] = 'div';
        $data['container_type'] = 'container';
    }

    return $data;
};
