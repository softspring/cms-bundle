<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Serialization;

use Softspring\MediaBundle\Model\MediaInterface;
use Softspring\MediaBundle\Model\MediaVersionInterface;
use Softspring\MediaBundle\Type\MediaTypesCollection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

use const DATE_ATOM;

class MediaImageSerializer
{
    public function __construct(
        private readonly MediaTypesCollection $mediaTypesCollection,
        private readonly MediaTypeRequirementsSerializer $requirementsSerializer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function typeRequirement(string $type, array $typeConfig): array
    {
        return $this->requirementsSerializer->describe($type, $typeConfig);
    }

    public function preview(MediaInterface $media): array
    {
        return [
            'title' => $media->getName() ?: sprintf('Media image %s', $media->getId()),
            'type' => $media->getType(),
            'private' => $media->getPrivate(),
            'description' => $media->getDescription(),
            'thumbnail' => $this->thumbnail($media),
            'adminUrl' => $this->adminUrl($media),
            'previewMarkdown' => $this->previewMarkdown($media),
        ];
    }

    public function context(MediaInterface $media): array
    {
        $typeConfig = $this->mediaTypesCollection->getType((string) $media->getType());

        return [
            'id' => $media->getId(),
            'type' => $media->getType(),
            'adminUrl' => $this->adminUrl($media),
            'previewMarkdown' => $this->previewMarkdown($media),
            'name' => $media->getName(),
            'description' => $media->getDescription(),
            'altTexts' => $media->getAltTexts(),
            'metadata' => $media->getMetadata(),
            'requirements' => $this->requirementsSerializer->describe((string) $media->getType(), $typeConfig),
            'thumbnail' => $this->thumbnail($media, true),
            'versions' => array_map(fn (MediaVersionInterface $version): array => $this->version($version, true), $media->getVersions()->toArray()),
        ];
    }

    public function thumbnail(MediaInterface $media, bool $includeInternalUrl = false): array
    {
        $thumbnail = $media->getVersion('_thumbnail');
        if ($thumbnail instanceof MediaVersionInterface) {
            return $this->version($thumbnail, $includeInternalUrl);
        }

        $summary = [
            'version' => '_thumbnail',
            'publicUrl' => null,
            'width' => null,
            'height' => null,
            'fileSize' => null,
            'mimeType' => null,
            'missing' => true,
        ];

        if ($includeInternalUrl) {
            $summary['url'] = null;
            $summary['uploadedAt'] = null;
            $summary['generatedAt'] = null;
        }

        return $summary;
    }

    public function version(MediaVersionInterface $version, bool $includeInternalUrl = false): array
    {
        $summary = [
            'version' => $version->getVersion(),
            'publicUrl' => $version->getPublicUrl(),
            'width' => $version->getWidth(),
            'height' => $version->getHeight(),
            'fileSize' => $version->getFileSize(),
            'mimeType' => $version->getFileMimeType(),
        ];

        if ($includeInternalUrl) {
            $summary['url'] = $version->getUrl();
            $summary['uploadedAt'] = $version->getUploadedAt()?->format(DATE_ATOM);
            $summary['generatedAt'] = $version->getGeneratedAt()?->format(DATE_ATOM);
        }

        return $summary;
    }

    public function adminUrl(MediaInterface $media): ?string
    {
        try {
            return $this->urlGenerator->generate('sfs_media_admin_medias_read', [
                '_locale' => $this->getLocale(),
                'media' => $media->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (Throwable) {
            return null;
        }
    }

    public function previewMarkdown(MediaInterface $media): string
    {
        $thumbnail = $this->thumbnail($media);
        $thumbnailUrl = $thumbnail['publicUrl'] ?? '';
        $title = $media->getName() ?: 'Media image';
        $adminUrl = $this->adminUrl($media) ?? '';

        return sprintf(
            "[![%s](%s)](%s)\n[%s](%s)",
            $this->escapeMarkdownText($title),
            $thumbnailUrl,
            $adminUrl,
            $this->escapeMarkdownText($title),
            $adminUrl,
        );
    }

    private function getLocale(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request?->attributes->get('_locale') ?: $request?->getLocale() ?: 'es';
    }

    private function escapeMarkdownText(string $text): string
    {
        return str_replace(['[', ']', '(', ')'], ['\\[', '\\]', '\\(', '\\)'], $text);
    }
}
