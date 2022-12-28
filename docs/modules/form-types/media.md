# Media type

This type depends on [softspring/media-bundle](https://github.com/softspring/media-bundle), and 
 **allows to select a media** instance in a select element.

This **media** type makes reference to *Softspring\CmsBundle\Form\Type\MediaType* form type (see *Softspring\MediaBundle\Form\MediaChoiceType*).

> *The "media" type replaces the deprecated "image" type, but it works exactly the same. It has been replaced because of semantic reasons.*

## Example usage

When you are modeling a CMS module, you can use this media field as follows:

```yaml
# cms/modules/example/config.yaml
module:
    module_options:
        form_fields:
            media_field:
                type: 'media'
                type_options:
                    media_types:
                        content:
                            image: 'sm'
                        background:
                            image: 'sm'
```

This example configuration requires a "content" media type in *softspring/media-bundle* configuration.

```yaml
# config/packages/sfs_media.yaml
sfs_media:
    types:
        content:
            name: 'Content image'
            upload_requirements: { minWidth: 600, minHeight: 100, mimeTypes: ['image/jpeg', 'image/png'],  }
            versions:
                _thumbnail: { scale_width: 300 }
                sm: { scale_width: 600 }
```

## Preview values

To use the media preview feature, you must include a preview identificator in a **data-media-preview-input** attribute: 

```yaml
# cms/modules/example/config.yaml
module:
    edit_template: '@module/example/edit.html.twig'
    module_options:
        form_fields:
            media_field:
                type: 'media'
                type_options:
                    attr:
                        data-media-preview-input: 'exampleMedia'
```

Now, the following twig template makes the preview work. You must include **data-media-preview-target** attribute with the
 same identificator as in *data-media-preview-input*.

```twig
{# cms/modules/example/edit.html.twig #}
<div data-media-preview-target="exampleMedia">
    {% if form.media_field.vars.data|default(false) %}
        {{ form.media_field.vars.data|sfs_media_render_image('sm') }}
    {% endif %}
</div>
```

**Preview also video medias**

Maybe, your media field contains only video elements or, like in the example below, both images and videos.

```twig
{# cms/modules/example/edit.html.twig #}
<div data-media-preview-target="exampleMedia">
    {% if form.media_field.vars.data|default(false) %}
        {% if form.media_field.vars.data.isImage() %}
            {{ form.media_field.vars.data|sfs_media_render_image('sm') }}
        {% elseif form.media_field.vars.data.isVideo() %}
            {{ form.media_field.vars.data|sfs_media_render_video('_original') }}
        {% endif %}
    {% endif %}
</div>
```

## Field rendering

Now you are ready to show your media image in the content render:

```twig
{# cms/modules/example/render.html.twig #}
{{ media_field|sfs_media_render_image('sm') }}
```

Remember that if it can contain images or videos:

```twig
{# cms/modules/example/render.html.twig #}
{% if media_field.isImage() %}
    {{ media_field|sfs_media_render_image('sm') }}
{% elseif media_field.isVideo() %}
    {{ media_field|sfs_media_render_video('_original') }}
{% endif %}
```

## Media attributes

In some cases, you want to add some attributes for the *sfs_media_render_image* or *sfs_media_render_video* twig filters.

There are several options to set them up, depending on the rendered type: *image_attr*, *video_attr*, *picture_attr*. Also
 a common options is available: *media_attr*.

The *media_attr* is appened to the other specific *\*_attr* options, so is a shared option.

The config file would be as follows:

```yaml
# cms/modules/example/config.yaml
module:
    module_options:
        form_fields:
            media_field:
                type: 'media'
                type_options:
                    media_attr:
                        class: 'col'
                    image_attr:
                        class: 'img-fluid'
                    video_attr:
                        controls: true
```

> *Keep in mind that specific option values (image_attr, video_attr, picture_attr) overrides the media_attr ones.*

You need to include those *\*_attr* options in the twig templates:

```twig
{# cms/modules/example/edit.html.twig #}
<div data-media-preview-target="exampleMedia">
    {% if form.media_field.vars.data|default(false) %}
        {% if form.media_field.vars.data.isImage() %}
            {{ form.media_field.vars.data|sfs_media_render_image('sm', form.media_field.vars.image_attr) }} 
        {% elseif form.media_field.vars.data.isVideo() %}
            {{ form.media_field.vars.data|sfs_media_render_video('_original', form.media_field.vars.video_attr) }}
        {% endif %}
    {% endif %}
</div>
```

In the render template you can access to the module configuration with the **_config** variable:

```twig
{# cms/modules/example/render.html.twig #}
{% if media_field.isImage() %}
    {{ media_field|sfs_media_render_image('sm', _config.module_options.form_fields.media.type_options.image_attr) }}
{% elseif media_field.isVideo() %}
    {{ media_field|sfs_media_render_video('_original', _config.module_options.form_fields.media.type_options.video_attr) }}
{% endif %}
```

### Working with videos

Usually, you will want to configure *\<video\>* tag attributes such as *autoplay* or *controls*. (View video [tag documentation](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/video) for all atributes).

This is a common configuration for those attributes:

```yaml
# cms/modules/example/config.yaml
module:
    module_options:
        form_fields:
            media_field:
                type: 'media'
                type_options:
                    video_attr:
                        autoplay: ''
                        loop: ''
                        muted: ''
                        playsinline: ''
                        controls: ''
```

This will render this tag:

```html
<video autoplay="" loop="" muted="" playsinline="" controls="" src="..."></video>
```

## Field Options

### media_types

**type:** *array* **default:** *[]*

This is the main configuration field, if no value provided the list of medias will be empty.

It must be an associative array with the media type as key, including a list of media *modes* and witch media version
 must be used.

For example, to use a version named *sm* as image: 

```yaml
media_types:
    content:
        image: 'sm'
```

For videos, you must use *video* attribute.

```yaml
media_types:
    content:
        video: '_original'
```

If instead of a simple image tag you want to use *pictures* (see *softspring/media-bundle*), you must configure the *picture* attribute:

```yaml
media_types:
    background:
        picture: '_default'
```

### media_attr

**type:** *array* **default:** *[]*

A list of attributes to be included in the resuting HTML tag.

