# Tags plugin documentation

## Quick Start Guide

Add the behavior to the model you want to tag:

```php
$this->addBehavior('Tags.Tag');
```

And in the add/edit forms you can use a basic text input:

```php
echo $this->Form->control('tag_list'); // input e.g.: Foo, Bar, Baz
```
This will be transformed into the array form automatically on save.

You can even just use the helper:

```php
echo $this->Tag->control();
```

Enjoy tagging!

## Usage



## Advanced features
By default the tags are counted (globally).
You can to add the column *tag_count* to the taggable table to also cache this counter for the specific types.


## Configuration
