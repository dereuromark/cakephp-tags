# Tags plugin documentation

## Quick Start Guide

Add the behavior to the model you want to tag:

```php
$this->addBehavior('Tags.Tag', ['taggedCounter' => false]);
```
If you want a tag counter in your tagged table, add a migration that adds a `tag_count` field into this table.
For now, we skip this.

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

Your edit action needs to contain the Tags relation to display existing tags into the form:
```php
// Inside get() call in the action
	'contain' => ['Tags'],
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
<?php
$this->loadHelper('Tags.TagCloud');

echo $this->TagCloud->display($tags, ['shuffle' => false], ['class' => 'tag-cloud']);
?>
```

With a bit of custom CSS you can make each tag a floating element.

By default the shuffle is enabled, you can disable using `'shuffle' => false` config as shown above.

### Advanced features
By default the tags are counted (globally).
You can add the column `counter` to the taggable table to also cache this counter for the specific types.

#### Validation
Don't forget to set up some basic validation on your tagged model.
You can re-use the same validation if you store it in a more central place.

#### Mass assignment
If you happen to set tags in a non-form context, you can just patch the entity manually:
```php
// $tags could be "Foo, Bar"
$this->Posts->patchEntity($post, ['tag_list' => $tags]);
$this->Posts->saveOrFail($post);
```

#### Search/Filter

You can easily combine the `tagged` custom finder with e.g. [Search](https://github.com/FriendsOfCake/search) plugin.
This way you can add a filter to your paginated index action.

Just pass a list of tags ([slug => name] pairs) down to the view layer where you populate the search form field as dropdown, for example:
```php
echo $this->Form->control('tag', ['options' => $tags, 'empty' => true]);
```

In your table's searchManager() configuration you will need a small callback config:
```php
$searchManager
	...
	->callback('tag', [
		'callback' => function (Query $query, array $args, $manager) {
			// Here you would have to remap $args if key isn't the expected "tag"
			$query->find('tagged', $args);
		}
	]);
```

##### Finding records without tags
For this the `tag_count` field (and check for 0) is the quickest and easiest.
If you didn't set up such a counter cache field, then you can also set up the callback query as:
```php
$this->hasOne('NoTags', ['className' => 'Tags.Tagged', 'foreignKey' => 'fk_id', 'conditions' => ['fk_model' => '...']]);
$query = $query->contain(['NoTags'])->where(['NoTags.id IS' => null]);
```
Your search form then might also have an additional value for this in the $tags array:
```php
$tags['-1'] = '- All without any tags -';
echo $this->Form->control('tag', ['options' => $tags, 'empty' => true]);
```
Then you just have to switch the query to the one above in the case of `-1`.

## Configuration
You can set the configuration globally in your app.php using the "Tags" key.
Or you can dynamically set it on each `addBehavior()` method call as well as when loading the helper.

The most important ones are:

- `'taggedCounter'`: Set to false if you don't need a counter cache field in your tagged table.
- `'slugBehavior'`: `true`/`false` (`true` = auto detect slugging, set to behavior otherwise, e.g. `'MyPlugin.MyCustomSlugger'`)
- `'strategy'`: `'string'`/`'array'`
- `'delimiter'` - Separating the tags, e.g.: `','`
- `'separator'`: For namespace prefix, e.g.: `':'`
