# Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```bash
$ composer require softspring/cms-bundle:^5.1
```

## Applications that don't use Symfony Flex

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require softspring/cms-bundle:^5.1
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Softspring\CmsBundle\SfsCmsBundle::class => ['all' => true],
];
```

### Step 3: Install yarn dependencies

Install node dependencies for assets building, please do it in this order:

```bash
yarn add "file:vendor/softspring/polymorphic-form-type/assets" --dev
yarn add "file:vendor/softspring/media-bundle/assets" --dev
yarn add "file:vendor/softspring/cms-bundle/assets" --dev
```

