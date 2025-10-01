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
    contain: ['Tags'],
```
This is also important for the patching part to avoid the ORM trying to re-add existing ones.

So a controller "edit" action usually still looks like always:
```php
    $article = $this->Article->get($id,
        contain: ['Tags'],
    );
    if ($this->request->is(['patch', 'post', 'put'])) {
        $article = $this->Articles->patchEntity($article, $this->request->getData());
        if ($this->Articles->save($article)) {
            $this->Flash->success(__('Post and its tags has been saved.'));

            return $this->redirect(['action' => 'view', $id]);
        }
        $this->Flash->error(__('The post could not be saved. Please, try again.'));
    }

    $tags = $this->Articles->Tags->find('list', ...['keyField' => 'slug']);
    $this->set(compact('article', 'tags'));
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

### Custom finders
They can also be combined/stacked with other custom finders, of course.

#### Tagged
```php
$taggedRecords = $this->Records->find('tagged', ...['tag' => 'tag-slug']);
```
It also accepts an array here to match any of these tags given.

Note: If you want to use label lookup instead of slug lookup, you need to configure the `finderField`.

#### Untagged
```php
$untaggedRecords = $this->Records->find('untagged');
```


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

#### Tag Colors
Tags support an optional `color` field (hex format like `#FF5733`) for visual labeling, similar to GitHub labels.

After running the plugin migrations, the color field is available. You can use it in forms:
```php
// In tag management forms
echo $this->Form->control('color', ['type' => 'color']);
```

And display colored tags in your templates:
```php
// When displaying tags
foreach ($entity->tags as $tag) {
    $bgColor = $tag->color ?? '#cccccc';
    echo '<span style="background-color: ' . h($bgColor) . '; padding: 2px 8px; border-radius: 3px;">';
    echo h($tag->label);
    echo '</span>';
}
```

The color field accepts standard hex color values (e.g., `#FF5733`) and is validated automatically.

#### Validation
Don't forget to set up some basic validation on your tagged model.
You can re-use the same validation if you store it in a more central place.

#### Patching
If you happen to set tags in a non-form context, you can just patch the entity manually:
```php
// $tags could be "Foo, Bar"
$this->Posts->patchEntity($post, ['tag_list' => $tags]);
$this->Posts->saveOrFail($post);
```

Make sure, that - when updating instead of creating tags - you contained the existing ones in the entity.
It should look somewhat like this before patching:
```
object(App\Model\Entity\Post) {
    ...
    'tags' => [
        object(Tags\Model\Entity\Tag) {
            'id' => 1,
            ...
            '_joinData' => object(Tags\Model\Entity\Tagged) {
                ...
            }
        },
        ...
    ]
}
```

After patching, it should contain the full list of existing/modified (`'new' => false`) and to be added entities (`'new' => true`) - and should not contain any to be deleted ones.

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
            $query->find('tagged', ...$args);
        }
    ]);
```

##### Or/And

Using `orSeparator`/`andSeparator` config, one can also filter by multiple tags at once:
```php
// Records that have either "one" or "two" tag
$query->find('tagged', ...['slug' => 'one,two']);

// Records that have both "one" and "two" tag
$query->find('tagged', ...['slug' => 'one+two']);
```

For this to be used inside Search plugin, you would usually generate URLs like this:
```php
$this->Html->link('Title', ['?' => ['tag'=> 'one,two']]);
$this->Html->link('Title', ['?' => ['tag'=> 'one+two']]);
```
Inside the URLs the special chars will automatically be URLencoded.

##### Finding records without tags
You can use the untagged finder here inside the search callback.
For this the `tag_count` field (and check for 0) is the quickest and easiest. It will otherwise
automatically fallback to a live lookup in the pivot table (tagged).

Your search form might now have an additional value for this in the `$tags` array:
```php
$tags['-1'] = '- All without any tags -';
echo $this->Form->control('tag', ['options' => $tags, 'empty' => true]);
```
Then you just have to switch the query inside the callback in the case of `-1`:
```php
    'callback' => function (Query $query, array $args, $manager) {
        if ($args['tag'] === '-1') {

        } else {
            $query->find('tagged',...$args);
        }
    }
```

#### Multiple tags per model
To have a behavior attached with different "tags" multiple times, a few config keys have to be overwritten or set.

Let's imagine MultiTagsRecords table and `one`, `two` tag collections.
```php
    $this->addBehavior('TagsOne', [
        'className' => 'Tags.Tag',
        'fkModelAlias' => 'MultiTagsRecordsOne',
        'field' => 'one_list',
        'tagsAlias' => 'TagsOne',
        'taggedAlias' => 'TaggedOne',
        'taggedCounter' => false,
        'tagsAssoc' => [
            'propertyName' => 'one',
        ],
        'implementedFinders' => [
            ...
        ],
        'implementedMethods' => [
            ...
        ],
    ]);
    $this->addBehavior('TagsTwo', [
        'className' => 'Tags.Tag',
        'fkModelAlias' => 'MultiTagsRecordsTwo',
        'field' => 'two_list',
        'tagsAlias' => 'TagsTwo',
        'taggedAlias' => 'TaggedTwo',
        'taggedCounter' => false,
        'tagsAssoc' => [
            'propertyName' => 'two',
        ],
        'implementedFinders' => [
            ...
        ],
        'implementedMethods' => [
            ...
        ],
    ]);
```

They important config key here is `fkModelAlias` which has to be unique per tag collection and therefore per loaded behavior instance.
Also make sure the finders and methods have been aliases to unique ones.

As for your helper call, that one now needs also to be loaded twice.
Or you use the inline config:
```php
echo $this->Tag->control(['field' => 'other_list']);
```

## Configuration
You can set the configuration globally in your app.php using the "Tags" key.
Or you can dynamically set it on each `addBehavior()` method call as well as when loading the helper.

The most important ones are:

- `'taggedCounter'`: Set to false if you don't need a counter cache field in your tagged table.
- `'strategy'`: `'string'`/`'array'`
- `'delimiter'` - Separating the tags in input elements or as string list, e.g.: `','`
- `'namespace'` - `'string'` to use for internal namespace column. Do not use together with separator if you want to keep it internal. Otherwise it will become the default namespace and visible.
- `'separator'`: For namespace prefix, e.g.: `':'`. With this set the namespace will be parsed from the tag.
- `'andSeparator'` - Allows AND filtering, e.g.: `'+'` or `'&'`
- `'orSeparator'` - Allows OR filtering, e.g.: `','` or `'|'`

You can set them globally using Configure and the `Tags` config key.

If you need also to pass options to the slug behavior, use an array config for it:
```php
'slugBehavior' => [
    'Tools.Slugged' => [
        'mode' => [Text::class, 'slug'],
        ...
],
```

### Custom slugging
If you want to use custom slugging, use the `'slug'` callable you can provide to the behavior:

```php
'slug' => function($tag) {
    return (new CustomSlugger())->slug($tag);
}
```

### UUIDs
By default, the plugin works with AIIDs (auto-incremental IDs). This usually suffices, as the tags are usually not exposes via ID, but via slug.
As such the internal ID is usually not leaking to the outside.
If you, for some reason, still need to use UUIDs, please copy over the schema to your project's `/config/Migrations/` folder and adjust the primary key in the migration files to `'type' => 'uuid', 'length' => 36, 'null' => false`.

Make sure you didn't add any validation like "numeric" here, only "scalar" ideally.
See the test cases (and fixtures for UUIDs) for details.

If you would like to tag (at least) one model that uses an UUID as a primary key, you will need to adjust the migration and change the foreign key field `fk_id` from `integer` to `uuid` as well.
This will work with UUIDs as well as with AIIDs.

### Entity Routing
If you create your own APP Tags controller, you can easily have EntityRouting set up for it:
```php
$routes->connect('/tag/:slug', ['controller' => 'Tags', 'action' => 'view'], ['routeClass' => 'EntityRoute']);
```
In your templates you can then build URLs with the entities passed along directly:
```php
echo $this->Html->link($tag->label,
    [
        'controller' => 'Tags',
        'action' => 'view',
        '_entity' => $tag,
    ]
);
```

Defining a route name you could even just use the short form `'_name' => 'my-tag-alias', '_entity' => $tag` for the links:
```php
$routes->get('/tag/:slug', ['controller' => 'Tags', 'action' => 'view'], 'my-tag-alias');
```

For details see [Core docs](https://book.cakephp.org/3.0/en/development/routing.html#entity-routing).

## Tips

### IDE support/help
For higher productivity use the [IdeHelper](https://github.com/dereuromark/cakephp-ide-helper/) plugin to auto-add the annotations for your new relations.

This will most likely add the following annotations to your table class:
```
 * @property \Tags\Model\Table\TaggedTable|\Cake\ORM\Association\HasMany $Tagged
 * @property \Tags\Model\Table\TagsTable|\Cake\ORM\Association\BelongsToMany $Tags
 * @mixin \Tags\Model\Behavior\TagBehavior
```
And also some in your entity:
```
 * @property \Tags\Model\Entity\Tagged[] $tagged
 * @property \Tags\Model\Entity\Tag[] $tags
```

For helper usage in the templates this will be added to AppView:
```
* @property \Tags\View\Helper\TagHelper $Tag
```

These will help you, your IDE and tooling like PHPStan to understand the relations and how to use them.
The IdeHelper will also give you autocomplete on those for all loadModel() calls as well as autocomplete on the custom finders.

The only manual annotation you will have to add, is the `tag_list` for the entity:
```
* @property string $tag_list !
```


### Make sure modified fields are `$_accessible`
You do not necessarily need to have:
```php
protected $_accessible = [
    '*' => true,
    'id' => false,
];
```
The TagsBehavior will usually automatically make the needed `tags` field accessible for patching.
If in doubt or if patching doesn't work as expected, double check if those fields have been properly made accessible.

Only if you need to store more data than the default fields, you might have to additionally whitelist those, as well.
