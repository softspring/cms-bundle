<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Serialization;

class MediaTypeRequirementsSerializer
{
    public function describe(string $type, array $typeConfig): array
    {
        return [
            'type' => $type,
            'label' => $typeConfig['label'] ?? $type,
            'mediaType' => $typeConfig['type'] ?? null,
            'isImage' => 'image' === ($typeConfig['type'] ?? null),
            'uploadRequirements' => $typeConfig['upload_requirements'] ?? [],
        ];
    }
}
