# Media Modal type

This type depends on [softspring/media-bundle](https://github.com/softspring/media-bundle), and
**allows to select a media** instance from a modal popup.

This **mediaModal** type makes reference to *Softspring\CmsBundle\Form\Type\MediaModalType* form type (see *Softspring\MediaBundle\Form\MediaModalType*).

> *The "mediaModal" type replaces the deprecated "imageModal" type, but it works exactly the same. It has been replaced because of semantic reasons.*

It's similiar to [media](media.md) type, but this *mediaModal* type has more features explained bellow.

## Example usage

When you are modeling a CMS module, you can use this *mediaModal* field as follows:

```yaml
# cms/modules/example/config.yaml
module:
    module_options:
        form_fields:
            media_field:
                type: 'mediaModal'
                type_options:
                    media_types:
                        content:
                            image: 'sm'
                        background:
                            image: 'sm'
```

This requires some *softspring/media-bundle* configuration (see [media form type](media.md) for more info).

## Preview values

Preview values works the same as [media form type](media.md).

## Field rendering

Field rendering works the same as [media form type](media.md).

## Media attributes

Media attributes works the same as [media form type](media.md).

## Thumbnails

This *mediaModal* type also allows to show a thumbnail of selected image:

```yaml
# cms/modules/example/config.yaml
module:
    module_options:
        form_fields:
            media_field:
                type: 'mediaModal'
                type_options:
                    show_thumbnail: true
```

## Upload new medias

> *This type will provide the feature of uploading new medias, but it's not yet implemented.*

## Field options

Same options as [media form type](media.md).

