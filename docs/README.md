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
$this->loadHelper('Tags.Tag');

echo $this->Tag->control();
```

Enjoy tagging!

## Usage

### Array strategy
If the default "string" strategy and comma separated text input list does not suffice, you can for example use "array" strategy.
This can be useful when working with select2 and dropdowns (`<select>`).
```php
echo $this->Tag->control();
```
in this case is equivalent with the manual version of
```php
echo $this->Form->control('tag_list', ['type' => 'select', 'multiple' => true, 'options' => ..., 'val' => ...]);
```

If you need more customization, use the `tags` property directly. 
When saving the tags, they need to be in the normalized form then on patching. 

### Tag Cloud
You can easily find and display all tags as cloud.

In your controller:
```php
$tags = $this->MyTaggedTable->Tagged->find('cloud')->toArray();
$this->set(compact('tags'));
```

In your template:
```php
<ul class="tag-cloud">
	<?php
	$this->loadHelper('Tags.TagCloud');

	echo $this->TagCloud->display($tags, ['before' => '<li style="font-size: %size%%">', 'after' => '</li>']);
	?>
</ul>
```

With a bit of custom CSS you can make each tag a floating element.

By default the shuffle is enabled, you can disable using `'shuffle' => false` as config.

### Advanced features
By default the tags are counted (globally).
You can to add the column *tag_count* to the taggable table to also cache this counter for the specific types.

#### Validation
Don't forget to set up some basic validation on your tagged model.
You can re-use the same validation if you store it in a more central place. 

## Configuration
You can set the configuration globally in your app.php using the "Tags" key.
Or you can dynamically set it on each `addBehavior()` method call as well as when loading the helper.

The most important ones are:

- `'slugBehavior'`: `true`/`false` (`true` = auto detect slugging, set to behavior otherwise, e.g. `'MyPlugin.MyCustomSlugger'`)
- `'strategy'`: `'string'`/`'array'`
- `'delimiter'` - separating the tags, e.g.: `','`
- `'separator'`: For namespace prefix, e.g.: `':'`
