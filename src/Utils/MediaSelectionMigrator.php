<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Utils;

use Softspring\MediaBundle\Helper\MediaSelectionChoices;
use Softspring\MediaBundle\Model\MediaInterface;
use Softspring\MediaBundle\Type\MediaTypesCollection;

/** Repairs module media selections using the same allow-list as the form. */
final class MediaSelectionMigrator
{
    public static function migrate(array $data, array $formFields, MediaTypesCollection $mediaTypes): array
    {
        foreach ($formFields as $name => $field) {
            if (!array_key_exists($name, $data)) {
                continue;
            }
            $data[$name] = self::migrateField($data[$name], $field, $mediaTypes);
        }

        return $data;
    }

    private static function migrateField(mixed $value, array $field, MediaTypesCollection $mediaTypes): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        $options = $field['type_options'] ?? [];
        if ('dynamicFormCollection' === ($field['type'] ?? null)) {
            foreach ($value as $key => $entry) {
                if (is_array($entry)) {
                    $value[$key] = self::migrate($entry, $options['entry_options']['form_fields'] ?? [], $mediaTypes);
                }
            }

            return $value;
        }
        if ('translatable' === ($field['type'] ?? null)) {
            $nestedField = ['type' => $options['type'] ?? null, 'type_options' => $options['type_options'] ?? []];
            foreach ($value as $locale => $selection) {
                $value[$locale] = self::migrateField($selection, $nestedField, $mediaTypes);
            }

            return $value;
        }
        if ('mediaVersion' !== ($field['type'] ?? null) || null === ($options['media_types'] ?? null)) {
            return $value;
        }
        $media = $value['media'] ?? null;
        if (!$media instanceof MediaInterface) {
            // Serialized references and missing media cannot be inspected safely here.
            return $value;
        }
        $choices = MediaSelectionChoices::forMedia($media, $options['media_types'], $mediaTypes->getTypes()[$media->getType()] ?? []);
        $selected = $value['version'] ?? null;
        if ($choices && (!is_string($selected) || !array_key_exists($selected, $choices))) {
            $value['version'] = array_key_first($choices);
        }

        return $value;
    }
}
