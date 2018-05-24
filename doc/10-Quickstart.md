ipl\Html
========

In this namespace you can find everything you need to build and render you HTML
components in a safe and comfortable way.

Base Principles
---------------

This library wants to give you the guarantee that every time you require an
implementation of the `ipl\Html\ValidHtml` Interface you'll not have to worry
about escaping HTML content at all. 

Rules
-----

* We deal with UTF-8 only. In case you want to handle other encodings, this is
  not the library you're looking for.
* This library has been written for HTML5
* All our attributes are enclosed with double quotes (`"`)

In case you write code implementing `ValidHtml`, you have to follow above rules.
In case you're just using the library, there shouldn't be much to worry about.  

Quick Start
-----------

### Escaping Strings

We all know, Text needs to be escaped when shipped via HTML, otherwise bad things
(read: XSS) might happen. That's why you need to escape your strings. In PHP
depending on your version you might be used to `htmlspecialchars` with a couple
of flags, combined with an encoding.

In case above rules fit your requirements, this should now be as easy as:

```php
<?php

use ipl\Html\Html;

echo Html::escape('This is true: 2 > 1');
```

The rendered result looks as expected, nothing special so far:

```html
This is true: 2 &gt; 1
```

### HTML Tags

Just, we do **not** want you to even use this escape function. To better explain
why, let's add some HTML tags:

```php
<?= Html::tag('h1', 'Hello there!');
```

```html
<h1>Hello there!</h1>
```

Alternatively, one might also want to call our magic static helper method like
this:

```php
<?= Html::h1('Hello there!');
```

This looks better and has the same effect, but the disadvantage that your PHP
IDE might not understand your code. Let's move on and try to inject some special
characters:

```php
<?= Html::tag('h1', 'Hello <> world!');
```

```html
<h1>Hello &lt;&gt; world!</h1>
```

As you can see, the content is getting escaped. You can even add an Array with
multiple strings:

```php
<?= Html::tag('h1', ['Hello', '<name>', 'out' ,'there!']);
```

### Separators

Escaping is fine...

```html
<h1>Hello&lt;name&gt;outthere!</h1>
```

...but we are missing some spaces. Of course we could add them to our strings,
but we can also tell the `HtmlElement` to separate it's content with a special
character:

```php
<?= Html::tag('h1', ['Hello', '<name>', 'out' ,'there!'])->setSeparator(' ');
```

```html
<h1>Hello &lt;name&gt; out there!</h1>
```

Much better, isn't it? You can separate with any kind of string you want:

```php
<?= Html::tag('h1', ['Hello', 'out' ,'there!'])->setSeparator(' * ');
```

```html
<h1>Hello * out * there!</h1>
```

### HTML Attributes

Optionally you might want to pass some attributes:

```php
echo Html::tag('p', ['class' => 'error'], 'Something failed');
```

```html
<p class="error">Something failed</p>
```

Attributes and no content is also fine:

```php
<?= Html::tag('ul', ['role' => 'menu']);
```

```html
<ul role="menu"></ul>
```

### Nested Elements

It's perfectly legal to use any `ValidHtml` element as content:

```php
<?= Html::tag('ul', ['role' => 'navigation'], Html::tag('li', 'A point'));
```

```html
<ul role="menu"><li>A point</li></ul>
```

Want to pass multiple elements at once? Use an Array:

```php
<?= Html::tag('ul', ['role' => 'menu'], [
    Html::tag('li', 'First point'),
    Html::tag('li', 'Second point'),
    Html::tag('li', 'Third point'),
]);
```

```html
<ul role="menu"><li>First point</li><li>Second point</li><li>Third point</li></ul>
```

You can mix HTML and non-HTML components:

```php
<?= Html::tag('p', [
    'Hi ',
    Html::tag('strong', 'there'),
    ', are you ok?'
]);
```

This works fine:

```html
<p>Hi <strong>there</strong>, are you ok?</p>
```

### Formatted Strings

Still, we do not consider this code being very readable. In case you ever used
`printf`, the following might come in useful:

```php
<?= Html::sprintf(
    'Hi %s, are you ok?',
    Html::tag('strong', 'there')
);
```

```html
Hi <strong>there</strong>, are you ok?
```
