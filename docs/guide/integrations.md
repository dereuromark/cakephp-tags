# Application integrations

## Search filters

With [FriendsOfCake Search](https://github.com/FriendsOfCake/search) configured on your table, add a callback to its search manager:

```php
$searchManager->callback('tag', [
    'callback' => function (\Cake\ORM\Query\SelectQuery $query, array $args): bool {
        if ($args['tag'] === '-1') {
            $query->find('untagged');
        } else {
            $query->find('tagged', value: $args['tag']);
        }

        return true;
    },
]);
```

Use slugs as dropdown keys. A string `-1` is an application convention for records without tags; the Tags plugin does not interpret it automatically. Keep the Search component and table behavior configuration required by Search.

```php
$tags = $this->fetchTable('Tags.Tags')->find('list', keyField: 'slug', valueField: 'label')->toArray();
$tags['-1'] = 'Without tags';
$this->set(compact('tags'));
```

```php
<?= $this->Form->control('tag', ['options' => $tags, 'empty' => true]) ?>
```

Namespace-specific applications should filter the tag options by namespace. The [tagged finder](./finders.md) accepts separator expressions for OR or AND filters.

## Entity routing

For a public controller implemented by your application, use CakePHP's [EntityRoute](https://book.cakephp.org/5.x/development/routing.html#entity-routing):

```php
use Cake\Routing\Route\EntityRoute;

$routes->connect('/tag/{slug}', ['controller' => 'Tags', 'action' => 'view'], [
    'routeClass' => EntityRoute::class,
    '_name' => 'public-tag',
    'pass' => ['slug'],
]);
```

```php
<?= $this->Html->link($tag->label, ['_name' => 'public-tag', '_entity' => $tag]) ?>
```

The application controller must resolve the slug and enforce its own access rules. This route is separate from the plugin admin routes.

## IDE annotations

[IdeHelper](https://dereuromark.github.io/cakephp-ide-helper/) can annotate the generated associations and behavior methods. Add the virtual input property manually on the host entity, using a type consistent with the strategy:

```php
/**
 * @property string $tag_list
 * @property \Tags\Model\Entity\Tag[] $tags
 */
```

Use `string[]` for `tag_list` with the array strategy.

## Optional icons

Add an application migration for a nullable `icon` string column on `tags_tags`, and extend your tag form to edit it. The plugin does not include that column.

```php
<?php
use Migrations\BaseMigration;

class AddTagIcon extends BaseMigration {
    public function change(): void {
        $this->table('tags_tags')
            ->addColumn('icon', 'string', ['limit' => 100, 'null' => true, 'default' => null])
            ->update();
    }
}
```

Render the icon with your chosen library, escaping the label while allowing the generated element:

```php
$icon = $tag->icon ? $this->Html->tag('i', '', ['class' => $tag->icon]) . ' ' : '';
echo $this->Html->tag('span', $icon . h($tag->label), ['escape' => false, 'class' => 'tag']);
```

Provide the matching icon CSS in your application. Keep tag labels and any custom fields covered by your application validation rules.
