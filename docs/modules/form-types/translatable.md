# Translatable type

This **translatable** type makes reference to *Softspring\CmsBundle\Form\Type\TranslatableType* form type.

This form type provides multiple language values for any field and type.

It uses the de *%kernel.enabled_locales%* and *%kernel.default_locale%* values to configure languages, as default.

## Translate texts

The most common use of this type is to translate texts.

> *The "translatedText" type is deprecated. You should use this method.*

```yaml
module:
    module_options:
        form_fields:
            description:
                type: "translatable"
                type_options:
                    type: "text"
```

The values are stored as a json object with locale as key:

```json
{
    "description": {
        "en": "Product",
        "es": "Producto",
        "de": "Produkt",
    }
}
```

## Render values

The way to render translated values is using it as an array, accessing it throw the locale:

```twig
{{ description[app.request.locale]|default('') }}
```

> It's recommended to include a *|default('')* or check if it's defined before using it, just in case 
> the locale does not exists yet, to prevent errors.

## Preview values

Preview values works similiar as contained type does, but accesing it throw the locale:

```yaml
module:
    module_options:
        form_fields:
            description:
                type: "translatable"
                type_options:
                    type: "text"
                    type_options:
                        attr:
                            data-edit-content-input: 'description'
```

```twig
{% for locale,localeField in form.description %}
    <p data-edit-content-target="description.{{ locale }}" data-lang="{{ locale }}" contenteditable="true">{{ localeField.vars.data|default('description')|raw }}</p>
{% endfor %}
```

## Translate medias

> *This documentation page is not yet written*

## Options

### type

**type:** *string* **required** 

Form type for children elements. You can use any type in the way that CMS modules use them:

**Using short names**

```yaml
module:
    module_options:
        form_fields:
            description:
                type: "translatable"
                type_options:
                    type: "text"
```

Keep in mind that it translates this type names as follows (this preference order):

1. *App\Form\Type\<cappitalized-type-name>Type*
2. *Softspring\CmsBundle\Form\Type\<cappitalized-type-name>Type*
3. *Symfony\Component\Form\Extension\Core\Type\<cappitalized-type-name>Type*

**Using fully qualified domain names**

You can specify a fully qualified domain name to use an specific form type,

```yaml
module:
    module_options:
        form_fields:
            description:
                type: "translatable"
                type_options:
                    type: "Symfony\Component\Form\Extension\Core\Type\ColorType"
```

or other namespace type:

```yaml
module:
    module_options:
        form_fields:
            description:
                type: "translatable"
                type_options:
                    type: "Vendor\FeatureBundle\Type\ExampleType"
```

### type_options

**type:** *array* **default:** *[]*

Configures the selected type options:

```yaml
module:
    module_options:
        form_fields:
            description:
                type: "translatable"
                type_options:
                    type: "choice"
                    type_options:
                        multiple: true
                        expanded: true
                        choices: 
                            example1: 1
                            example2: 2
```

### languages

**type:** *array* **default:** *%kernel.enabled_locales%*

### default_language

**type:** *string* **default:** *%kernel.default_locale%*

### children_attr

**type:** *array* **default:** *[]*


