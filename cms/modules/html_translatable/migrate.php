<?php

return static function (array $data, int $originVersion, int $targetVersion): array {
    if ($originVersion < 2 && $targetVersion >= 2) {
        $data['class'] = '';
    }

    return $data;
};
