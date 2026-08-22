# Twig PowerPack

⚠️ This project is in experimental phase.

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE)

This package provides code-quality features to your Twig templates:
1. [Type-safety checks for template context variables](#feat1).
2. [Register global data/resources from any template](#feat2).
3. [Instantiate classes in templates](#feat3).

Along with new functionalities:
- `|json_decode` filter


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

## Introduction

Twig templating engine is seriously lacking types....

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

#### Optional
While `nullable` still requires the variable to be **defined** (it only allows its value to be NULL), a
variable may legitimately not be supplied at all — a parameter of an included template that has a default
value, for instance. Use the `optional` keyword: the variable is then only type-checked when it is
provided, and its absence is not an error.

```twig
{% expect optional 'string' as CLASSES %}

<div class="{{ CLASSES|default('') }}">...</div>
```

`optional` **must be placed right after the tag name**, before every other keyword, so that a declaration
always reads in the same order (optionality, then nullability, then type). Any other position is a syntax
error. It can be combined with `nullable` (the variable may be missing, _or_ hold NULL) and with `array of`:

```twig
{% expect optional nullable 'App\\UI\\ViewModels\\Foo' as FOO %}
{% expect optional array of 'App\\UI\\ViewModels\\Foo' as ITEMS %}
```

#### Arrays
You can also check if a variable is an array of a given type by using the `array of` keywords:

```twig
{% expect array of 'App\\UI\\ViewModels\\Foo' as ARRAY %}

{% for foo in ARRAY %}
...
{% endfor %}
```

The item type is **required**: an array is only checkable through what it contains, so `{% expect 'array'
as ARRAY %}` is refused with a syntax error rather than accepted as a declaration guaranteeing nothing.
A heterogeneous array — a bag of unrelated fields — is a hint that the data should be exposed as a named
class instead (see [Objects](#objects) above).

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


---

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

#### Priority

As you cannot always predict in which order data will be registered, you'll sometime need to ensure a data comes first, for example in the case of a script library required by others. Then, add the 
`priority` keyword at the end of your tag followed by a priority number (lower values come first*).

Tags without priority always come after prioritized ones.

_Note: the order of data with the same priority (or undefined) is not guaranteed._

```twig
{% register '/last.js' %}
{% register '/second.js' priority 2 %}
{% register '/first.js' priority 1 %}

<!-- <script src="/first.js"></script> -->
<!-- <script src="/second.js"></script> -->
<!-- <script src="/last.js"></script> -->
```


---

### <a name="feat3"></a>3) Instantiate classes in templates

Although it's better to do it in the controller when possible, you may need to create class instances directly in a template. The `new(string $fqcn, ...$args)` function allows you to call the constructor of a given class:

```twig
{% include('Partials/Menu.twig') with {Menu: new('App\\UI\\Partials\\Menu',
    'Main menu',
    [
        {Label: 'Item 1', Href: '/url/to/item1'},
        {Label: 'Item 2', Href: '/url/to/item2'},
    ],
)} %}
```
Given the following View Model class:
```php
namespace App\UI\Partials;

final class Menu
{
    private string $name;
    private array $items;
    
    public function __construct(string $name, array $items)
    {
        $this->name = $name;
        $this->items = array_map(static fn($item) => new MenuItem($item), $items);
    }
}
```

#### Named arguments

Twig has no native way to target a variadic Twig function's underlying constructor parameters by name —
`new(class_name, params)` only exposes `class_name` and `params` to Twig, so a named argument can never
reach a constructor parameter through them. Passing a **single, non-empty associative array** (every key a
string) works around this: `new()` spreads it as PHP named arguments, exactly as `new $class(...$data)`
would:

```twig
{{ new('App\\UI\\ViewModels\\MenuLink', {Label: 'Item 1', Href: '/url/to/item1', Show: isSuperAdmin}) }}
```

This requires **PHP 8.0+**, the version that introduced named arguments and array spreading into them. There
is no fallback on PHP 7.4: the engine rejects the spread itself with `Error: Cannot unpack array with string
keys`, so templates targeting 7.4 must keep passing constructor arguments positionally.

Unknown keys and wrong-typed values are reported by PHP itself, against the constructor's own signature: a
key with no matching parameter raises `Error: Unknown named parameter "..."`, and a value of the wrong type
raises a `TypeError` — the same guarantees as calling the constructor directly from PHP.

Every other shape of `params` — no argument, several arguments, an empty array, or an array containing at
least one numeric key — is left untouched and passed positionally, exactly as before.

#### Positional escape hatch

If a class's constructor legitimately expects an associative array as one of its positional parameters,
spreading it as named arguments would be wrong. Use `new_positional(string $fqcn, ...$args)` instead: it
always passes its arguments positionally, whatever their shape:

```twig
{{ new_positional('App\\UI\\ViewModels\\RawPayload', {Label: 'Item 1', Href: '/url/to/item1'}) }}
```


## License

_Twig PowerPack_ is licensed under MIT license. See LICENSE file.



[ico-version]: https://img.shields.io/packagist/v/mediagone/twig-powerpack.svg
[ico-downloads]: https://img.shields.io/packagist/dt/mediagone/twig-powerpack.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg

[link-packagist]: https://packagist.org/packages/mediagone/twig-powerpack
[link-downloads]: https://packagist.org/packages/mediagone/twig-powerpack
