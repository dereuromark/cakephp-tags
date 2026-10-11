# Tag behavior

The behavior adds `Tags` and `Tagged` associations to the host table. Tag records live in `tags_tags`; links in `tags_tagged` store the tag ID, host record ID and model discriminator.

```php
$this->addBehavior('Tags.Tag', [
    'taggedCounter' => false,
    'strategy' => 'string',
]);
```

The default string strategy accepts a comma-separated `tag_list`. The array strategy accepts a list of labels instead:

```php
$this->addBehavior('Tags.Tag', [
    'taggedCounter' => false,
    'strategy' => 'array',
]);
```

Keep the [helper](./helper.md) strategy consistent with the behavior. Neither strategy expects tag IDs in `tag_list`. For an ID-based custom form, work with the association directly and supply the normalized entity data your application needs.

## Counters

`tagsCounter` defaults to `['counter']`, which updates each tag's global link count. `taggedCounter` defaults to `['tag_count']`, which requires that column on the host table. Disable it with `false`, use a field name string or supply a list of field names.

The host counter tracks links for its model discriminator. ORM saves maintain the counter; direct SQL updates bypass it. `TagsTable::recalculateCounters()` repairs global tag counters, not every host table's cached counts.

## Namespaces

`namespace` sets a stored default namespace. `separator` enables parsing a prefix from the input:

```php
$this->addBehavior('Tags.Tag', [
    'taggedCounter' => false,
    'namespace' => 'topics',
    'separator' => ':',
]);
```

An input such as `topics:CakePHP` stores a namespace and label. With a separator, form output retains each stored namespace prefix. An explicit empty prefix, such as `:CakePHP`, selects the unnamespaced tag even when a default namespace is configured.

## Inline colors

Inline color parsing is opt-in:

```php
$this->addBehavior('Tags.Tag', [
    'taggedCounter' => false,
    'inlineColorEditing' => true,
]);
```

`Important@red` or `Important@#FF5733` sets a color. With this mode enabled, `@` separates the label from the color; an unknown color drops the suffix rather than preserving it as label text. Leave it disabled for literal email-shaped labels. `colorMap` replaces the built-in named-color map.

## Custom slugs

Use a callable taking the label and returning a slug:

```php
$this->addBehavior('Tags.Tag', [
    'taggedCounter' => false,
    'slug' => fn (string $label): string => mb_strtolower(\Cake\Utility\Text::slug($label)),
]);
```

Direct TagsTable marshalling reads the global `Tags.slug` key. A behavior-specific callable applies to the labels normalized through that behavior. There is no `slugBehavior` configuration hook.

## Multiple collections

Give each behavior a unique discriminator, field, association names, properties and finder aliases:

```php
$this->addBehavior('TopicTags', [
    'className' => 'Tags.Tag',
    'fkModelAlias' => 'ArticlesTopics',
    'field' => 'topic_list',
    'tagsAlias' => 'TopicTags',
    'taggedAlias' => 'TopicTagged',
    'tagsAssoc' => ['propertyName' => 'topics'],
    'taggedCounter' => false,
    'implementedFinders' => [
        'topicTagged' => 'findTagged',
        'topicUntagged' => 'findUntagged',
    ],
    'implementedMethods' => [],
]);
```

Use another set of names for the second collection. Contain both associations when editing. Configure each Tag helper call with its corresponding field. Custom collection names must stay consistent across callbacks, queries and templates.

Tag identity combines the namespace and slug. The built-in tagged finders match the configured field across namespaces. For a namespace-specific lookup or public route, constrain both namespace and slug in your application query; a slug alone can identify several tags. Keep namespace spelling and case consistent with your database collation.
