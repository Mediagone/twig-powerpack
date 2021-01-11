# Twig PowerPack

⚠️ This project is in experimental phase.

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE)

This package provides:
1. [Type-safety checks for template context variables](#feat1).
2. [Register global data/resources from any template](#feat2).


## Installation
This package requires **PHP 7.4+** and **Twig 2+**.

Add it as Composer dependency:
```sh
$ composer require mediagone/twig-powerpack
```
If you're using Symfony, enable the extension in `services.yaml`:
```yaml
services:
    
    Mediagone\Twig\PowerPack\TwigPowerPackExtension:
        tags: [twig.extension]
```


## Features

### <a name="feat1"></a>1) Context Variables type-checking

Templates usually require specific external data, but there is no native way to check the type of supplied variables. The `expect` tag allows you to **declare required variables** in your Twig files, making them also **self-documenting**. If the data is invalid, an exception will be thrown.

#### Primitive types
Supported scalar types are: _bool, float, int_ and _string_.

```twig
{% extends 'layout.twig' %}

{% expect 'string' as TITLE %}
{% expect 'bool' as ENABLED %}
{% expect 'float' as AMOUNT %}
{% expect 'int' as COUNT %}
```
_Note: TITLE, ENABLED, AMOUNT and COUNT represent the names of required variables._

#### Objects
Because they don't guarantee any data structure, anonymous objects (_stdClass_) are not supported. However, usage of named classes is strongly encouraged to expose data in your templates. Therefore, a _Fully Qualified Class Name_ (FQCN) can also be supplied:
```twig
{% expect 'App\\UI\\ViewModels\\Foo' as FOO %}

{{ FOO.bar }}
```

#### Nullable
Sometimes, you may want to ensure that a variable is defined while making it optional by using the `nullable` keyword:
```twig
{% expect nullable 'App\\UI\\ViewModels\\Foo' as FOO %}

{% if FOO != null %}
...
{% endif %}
```

#### Arrays
You can also check if a variable is an array of a given type by using the `array of` keywords:

```twig
{% expect array of 'App\\UI\\ViewModels\\Foo' as ARRAY %}

{% for foo in ARRAY %}
...
{% endfor %}
```

Arrays can also be nullable:
```twig
{% expect nullable array of 'App\\UI\\ViewModels\\Foo' as ARRAY %}

{% if ARRAY != null %}
...
{% endif %}
```

Or contain nullable elements:
```twig
{% expect array of nullable 'App\\UI\\ViewModels\\Foo' as ARRAY %}

{% for foo in ARRAY %}
    {% if foo != null %}
    ...
    {% endif %}
{% endfor %}
```

And even nullable array of nullable elements!
```twig
{% expect nullable array of nullable 'App\\UI\\ViewModels\\Foo' as ARRAY %}
```

_Note: Checking array's items type might induce a slight overhead, but unless you have thousands of elements it should be negligible._



### <a name="feat2"></a>2) Register global data from any template

You may occasionally declare specific data in your templates, used in the global scope. For example if your templates dynamically add CSS classes to HTML body, or if they require optional CSS or JavaScript resources you only want to include on demand.

#### String Data

Short string data can be registered from anywhere in your templates using the `{% register <data> in <registry> %}` tag:
```twig
// Page.twig

{% extends 'Layout.twig' %}

{% register 'has-menu' in 'bodyClasses' %}
{% register 'responsive' in 'bodyClasses' %}

{% register '/css/few-styles.css' in 'styles' %}
{% register '/css/some-styles.css' in 'styles' %}

{% register '/js/custom-scripts.js' in 'scripts' %}

...
```

And retrieved elsewhere through the `registry()` function:
```html
// Layout.twig

<html>
    <head>
        ...
        
        {% for css in registry('styles') %}
        <link rel="stylesheet" href="{{ css }}" />
        {% endfor %}
        <!-- <link rel="stylesheet" href="/css/few-styles.css" /> -->
        <!-- <link rel="stylesheet" href="/css/some-styles.css" /> -->
    </head>
    <body class="{{ registry('bodyClasses')|join(' ') }}">
    <!-- <body class="has-menu responsive"> -->
        ...
        
        {% for js in registry('scripts') %}
        <script src="{{ js }}"></script>
        {% endfor %}
        <!-- <script src="/js/custom-scripts.js"></script> -->
    </body>
</html>
```


#### Optional registry clause

For convenience, the registry name can be automatically inferred from the data when it represents a _path with an extension_, making usage of `in <registry>` optional. The following lines are equivalent:

```twig
{% register '/styles.css' in 'css' %}
{% register '/styles.css' %}
```


#### Body Data
Because you may need longer or dynamically generated data, the tag also supports a block syntax to allow a content body to be provided. In this case you _cannot_ define data in the opening tag and _the registry clause is mandatory_:
`{% register in <registry> %} <body data> {% endregister %}`

For example if you want to declare inline scripts from a template:
```twig
// Page.twig
{% extends 'Layout.twig' %}

{% set name = 'world' %}

{% register in 'inlineJs' %}
    alert('Hello {{ name }}');
{% endregister %}
```
And include it at the end of the html page:
```html
// Layout.twig

<html>
    <body>
        ...
    
        <script>
        {% for js in registry('inlineJs') %}
            {{ js|raw }}
        {% endfor %}
        <!-- alert('Hello world'); -->
        </script>
    </body>
</html>
```


#### Unicity

Data can be declared as unique, so if multiple templates register the same value, it will be included only once. It's required most of the time, just add the `once` keyword to the tag:

```twig
{% register once '/styles.css' %} 

// Subsequent identical statements will be ignored
{% register once '/styles.css' %}
```

It also works with body data:
```twig
{% register once '/styles.css' %}
{% register once in 'css' %}/styles.css{% register %}  // ignored
```

However, unicity is only enforced **within the same registry**, so both following statements will be taken into account:
```twig
{% register once '/styles.css' in 'css' %}
{% register once '/styles.css' in 'styles' %}
```


## License

_Twig PowerPack_ is licensed under MIT license. See LICENSE file.



[ico-version]: https://img.shields.io/packagist/v/mediagone/twig-powerpack.svg
[ico-downloads]: https://img.shields.io/packagist/dt/mediagone/twig-powerpack.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg

[link-packagist]: https://packagist.org/packages/mediagone/twig-powerpack
[link-downloads]: https://packagist.org/packages/mediagone/twig-powerpack
