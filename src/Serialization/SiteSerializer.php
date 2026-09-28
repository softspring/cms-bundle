<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Serialization;

use Softspring\CmsBundle\Model\SiteInterface;

use function array_keys;

class SiteSerializer
{
    public function __construct(
        private readonly SensitiveValueSanitizer $sanitizer,
    ) {
    }

    public function summarize(SiteInterface $site, bool $includeRawConfig = false): array
    {
        $config = $site->getConfig() ?? [];
        $data = [
            'id' => $site->getId(),
            'canonical' => $this->canonical($site),
            'locales' => $config['locales'] ?? [],
            'defaultLocale' => $config['default_locale'] ?? null,
            'allowedContentTypes' => $config['allowed_content_types'] ?? [],
            'hosts' => $config['hosts'] ?? [],
            'metadataKeys' => array_keys($site->getMetadata() ?? []),
        ];

        if ($includeRawConfig) {
            $data['config'] = $this->sanitizer->sanitize($config);
            $data['metadata'] = $this->sanitizer->sanitize($site->getMetadata());
        }

        return $data;
    }

    public function context(SiteInterface $site, bool $includeConfig = false): array
    {
        $data = [
            'id' => $site->getId(),
            'canonical' => $this->canonical($site),
            'metadata' => $site->getMetadata(),
        ];

        if ($includeConfig) {
            $data['config'] = $site->getConfig();
        }

        return $data;
    }

    private function canonical(SiteInterface $site): array
    {
        return [
            'scheme' => $site->getCanonicalScheme(),
            'host' => $site->getCanonicalHost(),
            'port' => $site->getCanonicalPort(),
        ];
    }
}
