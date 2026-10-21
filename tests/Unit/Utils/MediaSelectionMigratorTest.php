<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Tests\Unit\Utils;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Utils\MediaSelectionMigrator;
use Softspring\MediaBundle\Model\MediaInterface;
use Softspring\MediaBundle\Model\MediaVersionInterface;
use Softspring\MediaBundle\Type\ConfigMediaTypeProvider;
use Softspring\MediaBundle\Type\MediaTypesCollection;

final class MediaSelectionMigratorTest extends TestCase
{
    private function types(): MediaTypesCollection
    {
        return new MediaTypesCollection([new ConfigMediaTypeProvider([
            'content' => ['pictures' => ['_default' => [], 'two_columns' => [], 'alternative' => []]],
            'video' => ['video_sets' => ['cms_preview' => []]],
        ])]);
    }

    private function media(string $type = 'content'): MediaInterface
    {
        $media = $this->createMock(MediaInterface::class);
        $media->method('getType')->willReturn($type);
        $media->method('isImage')->willReturn('content' === $type);
        $media->method('isVideo')->willReturn('video' === $type);
        $original = $this->createMock(MediaVersionInterface::class);
        $original->method('getVersion')->willReturn('_original');
        $media->method('getVersions')->willReturn(new ArrayCollection([$original]));

        return $media;
    }

    private function fields(array $allowed): array
    {
        return ['image' => ['type' => 'translatable', 'type_options' => ['type' => 'mediaVersion', 'type_options' => ['media_types' => $allowed]]]];
    }

    public function testInvalidSelectionUsesConfiguredPictureNotHardCodedDefault(): void
    {
        foreach (['_default', 'two_columns', 'alternative'] as $picture) {
            $data = ['image' => ['en' => ['media' => $this->media(), 'version' => 'image#_original']]];
            $result = MediaSelectionMigrator::migrate($data, $this->fields(['content' => ['picture' => $picture]]), $this->types());
            self::assertSame('picture#'.$picture, $result['image']['en']['version']);
            self::assertSame($data['image']['en']['media'], $result['image']['en']['media']);
        }
    }

    public function testValidSelectionIsNotReplacedByFirstOption(): void
    {
        $data = ['image' => ['en' => ['media' => $this->media(), 'version' => 'picture#alternative']]];
        self::assertSame($data, MediaSelectionMigrator::migrate($data, $this->fields(['content' => ['picture' => ['two_columns', 'alternative']]]), $this->types()));
    }

    public function testFirstAvailableChoiceIsUsedInConfiguredOrder(): void
    {
        $data = ['image' => ['en' => ['media' => $this->media(), 'version' => '_original']]];
        $result = MediaSelectionMigrator::migrate($data, $this->fields(['content' => ['picture' => ['missing', 'alternative', '_default']]]), $this->types());
        self::assertSame('picture#alternative', $result['image']['en']['version']);
    }

    public function testAllLocalesAndOtherDataArePreservedAndMigrationIsIdempotent(): void
    {
        $media = $this->media();
        $data = ['image' => ['en' => ['media' => $media, 'version' => '_original', 'extra' => 'keep'], 'de' => ['media' => $media, 'version' => 'picture#two_columns'], 'fr' => null], 'title' => ['en' => 'Keep me'], '_module' => 'two_columns'];
        $fields = $this->fields(['content' => ['picture' => 'two_columns']]);
        $result = MediaSelectionMigrator::migrate($data, $fields, $this->types());
        $expected = $data;
        $expected['image']['en']['version'] = 'picture#two_columns';
        self::assertSame($expected, $result);
        self::assertSame($result, MediaSelectionMigrator::migrate($result, $fields, $this->types()));
    }

    public function testVideoAndVideoSetsFollowConfiguration(): void
    {
        foreach (['video' => ['_original', 'video#_original'], 'video_sets' => ['cms_preview', 'videoSet#cms_preview']] as $mode => [$name, $expected]) {
            $data = ['image' => ['en' => ['media' => $this->media('video'), 'version' => '_original']]];
            $result = MediaSelectionMigrator::migrate($data, $this->fields(['video' => [$mode => $name]]), $this->types());
            self::assertSame($expected, $result['image']['en']['version']);
        }
    }

    public function testUnresolvableSelectionsAreNeverErasedOrReassigned(): void
    {
        foreach ([['content' => ['picture' => 'missing']], ['hero' => ['picture' => '_default']], []] as $allowed) {
            $data = ['image' => ['en' => ['media' => $this->media(), 'version' => '_original'], 'de' => ['media' => 'serialized-id', 'version' => '_original'], 'es' => ['media' => null, 'version' => null]]];
            self::assertSame($data, MediaSelectionMigrator::migrate($data, $this->fields($allowed), $this->types()));
        }
    }

    public function testUnrestrictedAndNonMediaFieldsAreUnchanged(): void
    {
        $data = ['image' => ['media' => $this->media(), 'version' => '_original'], 'title' => 'Keep'];
        self::assertSame($data, MediaSelectionMigrator::migrate($data, ['image' => ['type' => 'mediaVersion'], 'title' => ['type' => 'text']], $this->types()));
    }

    public function testCollectionsMigrateEveryEntryAndLocaleWithoutChangingKeysOrPlainMediaReferences(): void
    {
        $media = $this->media();
        $entryFields = $this->fields(['content' => ['picture' => 'two_columns']]);
        $entryFields['reference'] = ['type' => 'media', 'type_options' => ['media_types' => ['content' => ['picture' => '_default']]]];
        $fields = ['slides' => ['type' => 'dynamicFormCollection', 'type_options' => ['entry_options' => ['form_fields' => $entryFields]]]];
        $entry = ['image' => ['en' => ['media' => $media, 'version' => '_original'], 'de' => ['media' => $media, 'version' => 'image#sm']], 'reference' => $media, 'title' => 'Keep'];
        $data = ['slides' => ['slide-a' => $entry, 7 => $entry, 'empty' => null], 'other' => 'Unchanged'];
        $expected = $data;
        foreach (['slide-a', 7] as $key) {
            foreach (['en', 'de'] as $locale) {
                $expected['slides'][$key]['image'][$locale]['version'] = 'picture#two_columns';
            }
        }
        $result = MediaSelectionMigrator::migrate($data, $fields, $this->types());
        self::assertSame($expected, $result);
        self::assertSame($result, MediaSelectionMigrator::migrate($result, $fields, $this->types()));
    }
}
