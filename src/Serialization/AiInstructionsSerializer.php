<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Serialization;

final class AiInstructionsSerializer
{
    public static function summarize(array $config): array
    {
        $metadata = $config['meta'] ?? [];
        $instructions = is_array($metadata) ? ($metadata['ai_instructions'] ?? null) : null;

        return is_string($instructions) && '' !== trim($instructions) ? ['aiInstructions' => $instructions] : [];
    }
}
