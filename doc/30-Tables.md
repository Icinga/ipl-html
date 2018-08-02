ipl\Html\Table
==============

Creating tables is a common task, and as all tables have roughly the very same
structure there is a dedicated class helping you with this.

Your very first Table
---------------------

```php
<?php
 
use ipl\Html\Table;

echo new Table();
```

The output is pretty boring and not very helpful:

```html
<table></table>
```

You could now of course create a `tbody` tag, add some `tr` elements, each of
them with some `td` elements and so on. `ipl\Html\Table` comes with some helper
methods that want to make your life easier. One of them is `Table::row`. It
creates a new `tr` Html Element, and accepts an array as it's first parameter.

Let's give it a try:

```php
<?= Table::row([
    'app1.example.com',
    '127.0.0.1',
    'production'
])->setSeparator("\n");
```

As one might expect, output looks like this:

```html
<tr>
<td>app1.example.com</td>
<td>127.0.0.1</td>
<td>production</td>
</tr>
```

HTML does allow only specific elements to be added to a Table, so let's see what
happens when we add an arbitrary string:

```php
<?= (new Table())->add('Some <special> string!');
```

Some magic kicks in, as the output looks as follows: 

```html
<table>
<tbody>
<tr><td>Some &lt;special&gt; string!</td></tr>
</tbody>
</table>
```

Let's try again with an array:

```php
<?= (new Table())->add([
    'app1.example.com',
    '127.0.0.1',
    'production'
]);
```

Now let's add this row to a table:

<?= (new Table())->add(Table::tr([
  'app1.example.com',
  '127.0.0.1',
  'production'
]));
