# Twig PowerPack

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE)

This package provides:
- Type-safety checks for context variables (`require` tag)


## Installation
This package requires **PHP 7.4+** and **Twig 2+**.

Add it as Composer dependency:

```sh
$ composer require mediagone/twig-powerpack
```

## Features

### Require (Tag)

Templates usually require specific context variables, but there is no native way to check the type of supplied data. This tag allows you to **declare expected variables** in your Twig files, making them also **self-documenting**. If the data is invalid, an exception will be thrown.

#### Primitive types
Supported scalar types are: _bool, float, int_ and _string_.

```twig
{% extends 'layout.twig' %}

{% require 'string' as TITLE %}
{% require 'bool' as ENABLED %}
{% require 'float' as AMOUNT %}
{% require 'int' as COUNT %}
```
_Note: TITLE, ENABLED, AMOUNT and COUNT represent the names of required variables._

#### Objects
Because they don't guarantee any data structure, anonymous objects (_stdClass_) are not supported. However, usage of named classes is strongly encouraged to expose data in your templates. Therefore, a _Fully Qualified Class Name_ (FQCN) can also be supplied:
```twig
{% require 'App\\UI\\ViewModels\\Foo' as FOO %}

{{ FOO.bar }}
```

#### Nullable
Sometimes, you may want to ensure that a variable is defined while making it optional by using the `nullable` keyword:
```twig
{% require nullable 'App\\UI\\ViewModels\\Foo' as FOO %}

{% if FOO != null %}
...
{% endif %}
```

## License

_Twig PowerPack_ is licensed under MIT license. See LICENSE file.



[ico-version]: https://img.shields.io/packagist/v/mediagone/twig-powerpack.svg
[ico-downloads]: https://img.shields.io/packagist/dt/mediagone/twig-powerpack.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg

[link-packagist]: https://packagist.org/packages/mediagone/twig-powerpack
[link-downloads]: https://packagist.org/packages/mediagone/twig-powerpack
