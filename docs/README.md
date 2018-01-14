# Tags plugin documentation

## Quick Start Guide

Add the behavior to the model you want to tag:

```php
$this->addBehavior('Tags.Tag');
```

And in the add/edit forms you can use a basic text input:

```php
echo $this->Form->input('tag_list');
```

This will be transformed into the array form automatically on save.

Enjoy tagging!

## Usage



## Advanced features
TODO: You can to add the column *tag_count* to the taggable table.


## Configuration
