# Modules

> *This documentation page is not yet written*
 
## Form types

### CMS provided types

- block
- class
- color
- css
- dynamicFormCollection
- dynamicForm
- html
- id
- layout
- mediaAlt
- [mediaModal](modules/form-types/media-modal.md)
- [media](modules/form-types/media.md)
- route
- [symfonyRoute](modules/form-types/symfony-route.md)
- tinymce
- [translatable](modules/form-types/translatable.md)
- *user*

### Basic symfony types

#### Text Fields

- [text](https://symfony.com/doc/current/reference/forms/types/text.html)
- [textarea](https://symfony.com/doc/current/reference/forms/types/textarea.html)
- [email](https://symfony.com/doc/current/reference/forms/types/email.html)
- [integer](https://symfony.com/doc/current/reference/forms/types/integer.html)
- [money](https://symfony.com/doc/current/reference/forms/types/money.html)
- [number](https://symfony.com/doc/current/reference/forms/types/number.html)
- [password](https://symfony.com/doc/current/reference/forms/types/password.html)
- [percent](https://symfony.com/doc/current/reference/forms/types/percent.html)
- [search](https://symfony.com/doc/current/reference/forms/types/search.html)
- [url](https://symfony.com/doc/current/reference/forms/types/url.html)
- [range](https://symfony.com/doc/current/reference/forms/types/range.html)
- [tel](https://symfony.com/doc/current/reference/forms/types/tel.html)
- ~~[color](https://symfony.com/doc/current/reference/forms/types/color.html)~~ (this color widget is overriden by CMS color field)

#### Choice Fields

- [choice](https://symfony.com/doc/current/reference/forms/types/choice.html)
- [enum](https://symfony.com/doc/current/reference/forms/types/enum.html)
- [entity](https://symfony.com/doc/current/reference/forms/types/entity.html)
- [country](https://symfony.com/doc/current/reference/forms/types/country.html)
- [language](https://symfony.com/doc/current/reference/forms/types/language.html)
- [locale](https://symfony.com/doc/current/reference/forms/types/locale.html)
- [timezone](https://symfony.com/doc/current/reference/forms/types/timezone.html)
- [currency](https://symfony.com/doc/current/reference/forms/types/currency.html)

#### Date and Time Fields

- [date](https://symfony.com/doc/current/reference/forms/types/date.html)
- [dateInterval](https://symfony.com/doc/current/reference/forms/types/dateinterval.html)
- [dateTime](https://symfony.com/doc/current/reference/forms/types/datetime.html)
- [time](https://symfony.com/doc/current/reference/forms/types/time.html)
- [birthday](https://symfony.com/doc/current/reference/forms/types/birthday.html)
- [week](https://symfony.com/doc/current/reference/forms/types/week.html)

#### Other Fields

- [checkbox](https://symfony.com/doc/current/reference/forms/types/checkbox.html)
- [file](https://symfony.com/doc/current/reference/forms/types/file.html)
- [radio](https://symfony.com/doc/current/reference/forms/types/radio.html)

#### Symfony UX Fields


#### UID Fields

- [uuid](https://symfony.com/doc/current/reference/forms/types/uuid.html)
- [ulid](https://symfony.com/doc/current/reference/forms/types/ulid.html)

#### Field Groups

- [collection](https://symfony.com/doc/current/reference/forms/types/collection.html)
- [repeated](https://symfony.com/doc/current/reference/forms/types/repeated.html)

#### Hidden Fields

- [hidden](https://symfony.com/doc/current/reference/forms/types/hidden.html)

#### Buttons

- [button](https://symfony.com/doc/current/reference/forms/types/button.html)
- [reset](https://symfony.com/doc/current/reference/forms/types/reset.html)
- [submit](https://symfony.com/doc/current/reference/forms/types/submit.html)

#### Base Fields

- [form](https://symfony.com/doc/current/reference/forms/types/form.html)

### Use another form types

> *This is not yet written*

### Build your own form types for modules

> *This is not yet written*


### How to create or overwrite a module

A module consists of four files and translations:
- **config.yaml**: Definition of the module, version, file path for editing and its form, and the fields required by the module.
- **form.html.twig**: Form for editing, here you can order how you want the fields to be displayed.
- **edit.html.twig**: Layout for the module edition, which should show how the module will look like. With a series of data attributes you can link the form fields defined in the config.yaml and how they will look like.
- **render.html.twig**: Final rendering of the module, used in the preview action and in the final rendering of the page.

#### Create
You can create new modules to suit your project design needs.

In the folder your-project/cms/modules, add a folder with the name of the module you want to create, e.g. **rating**.

To see in detail how the creation of modules works, let's create the **rating module**, which will display an image, a title, a description, score and a button to see more.

It will also have other fields that will allow us to give more flexibility, Id of the module, CSS classes of the module, CSS classes for the title, classes for the button and background color of the module.

Add the following files inside the folder:

```
 - rating
    - translations #The languages your project needs
        - sfs_cms_modules.en.yaml
        - sfs_cms_modules.es.yaml
    - config.yaml
    - form.html.twig
    - edit.html.twig
    - render.html.twig
```
##### config.yaml

```
module:
    revision: 1
    group: 'block' #Group in which will be included in the modal for module selection
    edit_template: '@module/rating/edit.html.twig' #Edit layout
    form_template: '@module/rating/form.html.twig' #Form layout
  
    module_options:
        form_fields: #Module field definition
            id:
                type: 'id' #Field type
                type_options:
                    attr:
                        data-edit-id-input: 'ratingId' #This data attribute allows the ID to be previewed during editing.
      
            class:
                type: 'class' #Field type
                type_options:
                    attr:
                        data-edit-class-input: 'ratingClasses' #This data attribute allows the CSS classes to be previewed during editing.
      
            media:
                type: 'translatable' #Images can be translatable or not
                type_options:
                      type: 'mediaVersion' #Field type
                      type_options:
                          media_attr:
                              class: 'img-fluid' #Defaul class
                          attr:
                              data-media-preview-input: 'media' #This data attribute allows the image to be previewed during editing.
      
            bg_color:
                type: 'color' #Field type
                type_options:
                    attr:
                        data-edit-bgcolor-input: 'ratingBgColor' #This data attribute allows the background color  to be previewed during editing.
      
      
            title:
                type: 'translatable' #Texts can be translatables or not
                type_options:
                    type: 'text' #Field type
                    type_options:
                        attr:
                            data-edit-content-input: 'title' #This data attribute allows the title text to be previewed during editing.
                    
            title_type:
                type: 'choice'
                type_options:
                    default_value: h2 #Default select value
                    choices:
                        "title.form.type.values.h1": h1
                        "title.form.type.values.h2": h2
                        "title.form.type.values.h3": h3
                        "title.form.type.values.h4": h4
                        "title.form.type.values.h5": h5
                        "title.form.type.values.h6": h6
                    attr:
                        data-cms-module-title-type-field: ''
                    
            title_class:
                type: 'class' #Field type
                type_options:
                    attr:
                        data-edit-class-input: 'titleClass' #This data attribute allows the title CSS classes to be previewed during editing.
      
            description:
                type: 'translatable'
                type_options:
                    type: 'html' #Field type
                    type_options:
                        attr:
                            data-edit-content-input: 'description' #This data attribute allows the description to be previewed during editing.
                            data-wysiwyg: '' #With this attribute opens the wysiwyg modal
        
            read_more_button_text:
                type: 'translatable'
                type_options:
                    type: 'text' #Field type
                    type_options:
                        attr:
                                data-edit-content-input: 'readMoreButtonText' #This data attribute allows the button text to be previewed during editing.
      
            read_more_button_link:
                type: 'symfonyRoute' #Field type: Symfony route selector
                type_options:
      
            read_more_button_class:
                type: 'class' #Field type
                type_options:
                    attr:
                        data-edit-class-input: 'readMoreButtonClass' #This data attribute allows the button CSS classes to be previewed during editing.
      
             score:
                type: 'choice' #Field type
                type_options:
                    choices:
                        "rating.form.score.values.one": 1
                        "rating.form.score.values.two": 2
                        "rating.form.score.values.three": 3
                        "rating.form.score.values.four": 4
                        "rating.form.score.values.five": 5
                    attr:
                        data-edit-class-input: 'scoreClass'


```

##### translations/sfs_cms_modules.en.yaml

```
rating:
    label: "Rating"
    prototype_button: "Rating"
    prototype_button_icon: '<i class="bi bi-star"></i>' #Icon in module selector modal
    form:
        locale_filter.label: "Locales" #If your application has languages, a language selector will appear to control the display of the module by language.
        site_filter.label: "Sites"
        background.label: "Background image"
        media.label: "Image"
        id.label: "Id"
        class.label: "Classes"
        title_class.label: "Title classes"
        title:
            label: "Title"
            placeholder: "Rating title"
        description:
            label: "Description"
            placeholder: "Rating description"
        read_more_button_text.label: "Button text"
        read_more_button_link:
            route_name.label: "Button link"
            route_params.label: "Link parameters"
        bg_color.label: "Background color"
        read_more_button_class.label: "Button classes"
        score.label: "Score"
        score.values:
            one: "1 star"
            two: "2 stars"
            three: "3 stars"
            four: "4 stars"
            five: "5 stars"
            
```
##### form.yaml

```
<<div class="row">
    <div class="col-12">
        {{ form_row(form.id) }}
    </div>
    <div class="col-12">
        {{ form_row(form.class) }}
    </div>
    <div class="col-12">
        {{ form_row(form.media) }}
    </div>
    <div class="col-12">
        {{ form_row(form.title) }}
    </div>
    <div class="col-12">
        {{ form_row(form.title_type }}
    </div>
    <div class="col-12">
        {{ form_row(form.title_class) }}
    </div>
    <div class="col-12">
        {{ form_row(form.score) }}
    </div>
    <div class="col-12">
        {{ form_row(form.read_more_button_text) }}
    </div>
    <div class="col-12">
        {{ form_widget(form.read_more_button_link) }}
    </div>
    <div class="col-12">
        {{ form_row(form.read_more_button_class) }}
    </div>
    <div class="col-12">
        {{ form_row(form.bg_color) }}
    </div>
</div>
{{ form_rest(form) }}

```

##### edit.yaml

```
{% set defaultRatingClass = 'sfs-rating' %}
{% set titleType = form.title_type.vars.data|default('h2') %}
<div
        id="{{ form.id.vars.data|default('') }}"
        data-edit-id-target="ratingId"

        class="{{ defaultRatingClass }} {{ form.class.vars.data|default('') }}"
        data-edit-class-target="ratingClasses"
        data-edit-class-default="{{ defaultRatingClass }}"

        data-edit-bgcolor-target="ratingBgColor"
        style="{{ form.bg_color.vars.data ? 'background-color:'~form.bg_color.vars.data : '' }}"
>
    <div class="sfs-rating__content">
        {% for locale,localeField in form.media %}
            <div class="sfs-rating__media" data-media-preview-target="media.{{ locale }}" data-lang="{{ locale }}">
                {% if localeField.media.vars.data %}
                    {{ localeField.media.vars.data|sfs_media_render(localeField.version.vars.data, localeField.media.vars.image_attr) }}
                {% endif %}
            </div>
        {% endfor %}

        {% for locale,localeField in form.title %}
            {% set titleClassDefault = 'sfs-rating__title' %}
            <{{ titleType }}
            class="{{ titleClassDefault }} {{ form.title_class.vars.data|default('') }}"

            data-cms-module-title-type=""

            data-edit-class-target="titleClass"
            data-edit-class-default="{{ titleClassDefault }}"

            data-lang="{{ locale }}"
            data-edit-content-target="title.{{ locale }}"
            data-edit-content-placeholder="{{ 'rating.form.title.placeholder'|trans({}, 'sfs_cms_modules', locale) }}"
            contenteditable="true"
            >{{ localeField.vars.data|default('')|raw }}</{{ titleType }}>
        {% endfor %}

        <span class="sfs-rating__stars sfs-rating__stars--{{ form.score.vars.value|default('') }}"
              data-edit-class-target="scoreClass"
              data-edit-class-default="sfs-rating__stars">
                {% for i in 1..5 %}
                    {% if i <= form.score.vars.value|default(1) %}
                        <i class="bi bi-star-fill"></i>
                    {% else %}
                        <i class="bi bi-star"></i>
                    {% endif %}
                {% endfor %}
        </span>

        {% for locale,localeField in form.description %}
            <div class="sfs-rating__desc mb-4 wysiwyg-preview"

                 data-lang="{{ locale }}"
                 data-edit-content-target="description.{{ locale }}"
                 data-edit-content-placeholder="{{ 'rating.form.description.placeholder'|trans({}, 'sfs_cms_modules', locale) }}"

                 data-bs-toggle="modal"
                 data-bs-target="#{{ id }}Modal"
            >{{ localeField.vars.data|default('')|raw }}</div>
        {% endfor %}

        <div class="sfs-rating__cta">
            {% for locale,localeField in form.read_more_button_text %}
                {% set defaultReadMoreButtonClass = 'btn btn-primary' %}

                <a href="#" data-edit-content-hide-if-empty="true"
                   class="{{ form.read_more_button_class.vars.data|default(defaultReadMoreButtonClass) }}"
                   data-edit-class-target="readMoreButtonClass"
                   data-edit-class-default="{{ defaultReadMoreButtonClass }}"
                   data-lang="{{ locale }}" data-edit-content-target="readMoreButtonText.{{ locale }}" contenteditable="true">{{ localeField.vars.data|default('') }}</a>
            {% endfor %}
        </div>
    </div>

    <!-- WYSIWYG Modal -->
    <div class="modal fade" id="{{ id }}Modal" data-wysiwyg-modal data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="{{ id }}ModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Card description</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {% for locale,localeField in form.description %}
                        <div data-edit-content-target="description.{{ locale }}" data-lang="{{ locale }}">
                            {{ form_row(localeField,{'attr':{'data-initial-value' : form.description.children[locale].vars.value }}) }}
                        </div>
                    {% endfor %}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>
</div>
```

##### render.yaml

```
<div id="{{ id|default('') }}" class="sfs-rating {{ class|default('') }}" style="{{ bg_color|default(false) ? 'background-color:'~bg_color : '' }}">
    <div class="sfs-rating__content">
        {% if media[app.request.locale]|default(false) %}
            <div class="sfs-rating__media">
                {{ media[app.request.locale].media|sfs_media_render(media[app.request.locale].version, {'class':'img-fluid'}) }}
            </div>
        {% endif %}
        <{{ title_type }} class="sfs-rating__title {{ title_class|default('') }}">{{ title|sfs_cms_trans|raw }}</{{ title_type }}>
        <span class="sfs-rating__stars">
            {% for i in 1..5 %}
            {% if i <= score|default(1) %}
                <i class="bi bi-star-fill"></i>
            {% else %}
                <i class="bi bi-star"></i>
            {% endif %}
            {% endfor %}
        </span>
        {% if description|default(false) %}<div class="sfs-rating__desc">{{ description|sfs_cms_trans|raw }}</div>{% endif %}
        <div class="sfs-rating__cta">
            {% if read_more_button_text|default(false) and read_more_button_text|sfs_cms_trans is not empty %}
                <a href="{{ sfs_cms_url(read_more_button_link) }}" {{ sfs_cms_route_attr(read_more_button_link)}} class="{{ read_more_button_class|default('btn btn-primary') }}">{{ read_more_button_text|sfs_cms_trans|raw }}</a>
            {% endif %}
        </div>
    </div>
</div>

```
> The module styles can be created in your project with the rest of the assets. 

> IMPORTANT: Clear cache when adding a new module. (php bin/console cache:clear --env=dev)

##### Final result
![Rating](https://storage.googleapis.com/mailingimg/softspring/cms/module-rating.png)


#### Overwrite

In the folder your-project/cms/modules, add a folder with the same name as the module you want to overwrite, for example, card.

You can add fields by overwriting config.yaml, if you need to layout the edit form, overwrite form.html.twig. For the editing layout, edit edit.html.twig. And for the rendering of the module to paint the new field, overwrite render.html.twig.

As an example we are going to change the button field, so that the CSS classes are not the bootstrap ones but some custom classes.

```
 - button
    - translations #The languages your project needs
        - sfs_cms_modules.en.yaml
    - config.yaml
```

##### config.yaml

```
module:
    revision: 3
    group: 'basic'
    edit_template: '@module/button/edit.html.twig'
    form_template: '@module/button/form.html.twig'

    module_options:
        form_fields:
            id:
                type: 'id'
                type_options:
                    attr:
                        data-edit-id-input: 'buttonId'

            button_style:
                type: 'choice'
                type_options:
                    choices: #Change choices with the custom classes
                        "button.form.button_style.values.read_more": 'btn-read-more'
                        "button.form.button_style.values.continue": 'btn-continue'
                        "button.form.button_style.values.save": 'btn-save'
                        "button.form.button_style.values.none": ''
                    attr:
                        data-edit-class-input: 'buttonClass'

            button_classes:
                type: 'class'
                type_options:
                    attr:
                        data-edit-class-input: 'buttonClass'

            button_text:
                type: 'translatableText'
                type_options:
                    children_attr:
                        data-edit-content-input: 'buttonText'

            button_link:
                type: 'symfonyRoute'
                type_options:

```

##### translations/sfs_cms_modules.en.yaml

```
button:
    label: "Button"
    prototype_button: "Button"
    prototype_button_icon: '<i class="bi bi-link"></i>'
    form:
        locale_filter.label: "Locales"
        site_filter.label: "Sites"
        id.label: "Id"
        class.label: "Classes"
        button_text.label: "Button text"
        button_link:
            route_name.label: "Button link"
            route_params.label: "Link parameters"
        button_style:
            label: "Button style"
            values:
                read_more: "Read more"
                continue: "Continue"
                save: "Save"
                none: "None"
            button_classes:
                label: "Custom butom styles (CSS classes)"
```
