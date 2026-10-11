# Finding records

Attach the [Tag behavior](./behavior.md) to the host table before using these finders.

## One or more tags

The default finder compares slugs:

```php
$articles = $this->fetchTable('Articles');
$query = $articles->find('tagged', value: 'cakephp');
```

An array matches any listed tag. The configured separators support OR and AND expressions:

```php
$any = $articles->find('tagged', value: ['cakephp', 'tutorials']);
$either = $articles->find('tagged', value: 'cakephp,tutorials');
$both = $articles->find('tagged', value: 'cakephp+tutorials');
```

`orSeparator` defaults to `,`; `andSeparator` defaults to `+`. Set either to `null` to disable parsing that operator. The AND parser runs first when the input contains both separators; it is not a general expression parser. Use separate finder calls for more complex conditions.

For label-based lookup, set `finderField => 'label'` in the behavior configuration. Queries can be combined with your own conditions and pagination.

## Records without tags

```php
$query = $articles->find('untagged');
```

With a configured host counter, this checks that field for zero. With `taggedCounter => false`, it checks the pivot association. If several counter fields are configured, pass the one to use:

```php
$query = $articles->find('untagged', counterField: 'tag_count');
```

Counter-based results require accurate counters. Bulk SQL changes bypass ORM counter-cache updates.

## Tag clouds

Query the pivot table associated with your host table:

```php
$tags = $articles->Tagged->find('cloud')->toArray();
$this->set(compact('tags'));
```

The cloud finder groups pivot rows by tag and adds a calculated `weight`. This query is global unless you add a condition for the model discriminator:

```php
$tags = $articles->Tagged->find('cloud')
    ->where(['Tagged.fk_model' => $articles->getAlias()])
    ->toArray();
```

In the template:

```php
$this->loadHelper('Tags.TagCloud');
echo $this->TagCloud->display($tags, ['shuffle' => false], ['class' => 'tag-cloud']);
```

Provide application CSS for the cloud classes. The helper shuffles entries by default; `shuffle => false` preserves their order.
