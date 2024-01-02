# Preview texts

This example stores a title field into the module.

```yaml
module:
    module_options:
        form_fields:
            title:
                type: 'text'
```

You can preview it adding to the edit template:

```twig
<h1>{{ form.title.vars.data|default('') }}</h1>
```

It's recommended to be conservative, to prevent errors, so add a *default* filter.

**Using placeholders**

```twig
<h1 data-edit-content-placeholder="Write the title!">{{ form.title.vars.data|default('') }}</h1>
```

**Content editable**

```yaml
module:
    module_options:
        form_fields:
            title:
                type: 'text'
                attr:
                    data-edit-content-input: 'title'
```

```twig
<h1 contenteditable="true" data-edit-content-target="title">{{ form.title.vars.data|default('')|raw }}</h1>
```

> Important: if you use a *display block* element, when you add a new line it will append a &lt;div&gt; tag.
> Use a inner *inline-block* element to prevent this behaviour.  

> Important: it's recommended to add a *raw* filter, to render HTML tags properly.

```twig
<h1><span contenteditable="true" data-edit-content-target="title">{{ form.title.vars.data|default('') }}</span></h1>
```

**Work with translated texts**

```yaml
module:
    module_options:
        form_fields:
            title:
                type: 'translatable'
                type_options:
                    type: 'text'
```

```twig
{% for locale,localeField in form.title|filter((f, locale) => not (locale starts with '_')) %}
    <h1 data-lang="{{ locale }}" contenteditable="true" data-edit-content-target="title.{{ locale }}">{{ localeField.vars.data|default('') }}</h1>
{% endfor %}
```
