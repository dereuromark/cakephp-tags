# Upgrading from 2.x to 3.0

cakephp-tags 3.0 is a breaking release focused on modernizing the finder API
and removing one dead config shape. Requirements are unchanged: PHP 8.2+,
CakePHP 5.1+.

## Automated migration

Most call-site changes can be applied with the cakephp/upgrade rector:

```
composer require --dev cakephp/upgrade
vendor/bin/cake upgrade rector --rules=named_arguments src/
```

What rector cannot rewrite is documented inline below.

## Breaking changes

### 1. `find('tagged')` — call-site key collapsed to `value`

The dynamic call-site key (`slug` or `label`, mirroring the `finderField`
config) has been replaced by a single fixed parameter named `value`. The
`finderField` config still controls which Tags column is matched against,
so only the call-site shape changes.

```php
// Before
$query->find('tagged', ['slug'  => 'example-tag']);
$query->find('tagged', ['label' => ['one', 'two']]);

// After
$query->find('tagged', value: 'example-tag');
$query->find('tagged', value: ['one', 'two']);
```

Rector will not rewrite this — manual change required.

### 2. `find('untagged')` — named `counterField` argument

```php
// Before
$query->find('untagged', ['counterField' => 'my_count']);

// After
$query->find('untagged', counterField: 'my_count');
```

The `counterField => false` overload that previously forced a live pivot
lookup has been removed. The live path is auto-selected when no counter
cache field is configured, so the explicit override turned out to be
redundant. If you actually need to force a live lookup despite a counter
cache being configured, please open an issue.

### 3. Method rename: `findByTag` → `findTagged`

The method backing the `tagged` finder alias has been renamed so it matches
its alias. Calls through `$query->find('tagged', ...)` are unaffected.
Direct method calls must be renamed:

```php
// Before
$table->findByTag($query, ['slug' => 'foo']);

// After
$table->findTagged($query, value: 'foo');
```

### 4. `taggedCounter` config — flat shape only

The nested `['field' => ['conditions' => []]]` shape was a no-op (its
per-field `conditions` value was overwritten internally) and has been
removed. Accepted shapes after 3.0:

```php
// All still work
'taggedCounter' => false,                          // disable counter cache
'taggedCounter' => 'tag_count',                    // single field shorthand
'taggedCounter' => ['tag_count', 'other_count'],   // list of fields

// No longer supported — unwrap to the flat list above
'taggedCounter' => ['tag_count' => ['conditions' => []]],
```

If you used the nested form, just unwrap to the flat list.

### 5. `prepareTagsForOutput()` split into strict-typed siblings

Two new strict-typed methods are available; the original method is kept as
a dispatcher and keeps its `array|string` return type, so direct callers
are unaffected.

```php
$behavior->prepareTagsForOutputArray($data);   // : array
$behavior->prepareTagsForOutputString($data);  // : string
```

## Non-breaking changes

- `normalizeTags()` now declares `array|string $tags` natively (was
  docblock-only). No behavior change.
