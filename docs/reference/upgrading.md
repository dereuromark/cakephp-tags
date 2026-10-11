# Upgrading

The active branch targets CakePHP 5. Composer declares the exact supported PHP and CakePHP ranges. CakePHP 6 support has not been declared.

For the earlier major-version migration, retain the repository's [UPGRADE.md](https://github.com/dereuromark/cakephp-tags/blob/master/UPGRADE.md).

## Current behavior

- Colored tag spans escape their style attribute, matching the linked-tag path.
- List-based tag finders use the host table primary key and SQL alias, including when separate collections have custom model discriminators.
- Standalone admin mode bypasses host initialization and `beforeFilter()` callbacks.
- Host redirects and stopped events are preserved before the optional plugin gate runs.

Application callbacks and middleware still control access. Review the [admin configuration](../guide/admin.md) if you use standalone mode or the optional gate.

## Replace old examples

Use `fetchTable()` and the correct table variable in controller examples. Load the current Tags association before patching an existing entity. Use named finder arguments, such as `find('tagged', value: 'cakephp')`.

The default host counter needs a `tag_count` column. Disable it for the simplest setup. `TagsTable::recalculateCounters()` rebuilds global tag counts; it does not backfill every host model.

The supplied routes place the dashboard at `/admin/tags` and the Tags controller at `/admin/tags/tags`. Entity routes use current brace placeholders, such as `/tag/{slug}`, and require an application controller that implements the public action.

## From 2.x to 3.x

The tagged finder now accepts the fixed `value` argument. Its configured `finderField` still determines whether values match slugs or labels:

```php
$query->find('tagged', value: 'example-tag');
$query->find('tagged', value: ['one', 'two']);
```

The untagged finder accepts a named `counterField` argument:

```php
$query->find('untagged', counterField: 'my_count');
```

The old `counterField => false` override is removed. Disable the behavior's `taggedCounter` configuration to use a live pivot lookup.

Direct calls to `findByTag()` must become `findTagged()`. Calls through the `tagged` finder alias retain that alias.

`taggedCounter` accepts `false`, a field name or a flat list of field names. Replace the old nested field-condition map with the list. Per-field nested conditions were overwritten internally.

`prepareTagsForOutput()` remains an array-or-string dispatcher. The typed `prepareTagsForOutputArray()` and `prepareTagsForOutputString()` methods are available for callers that need a specific output shape.

## Polymorphic schema

Fresh migrations retain the configured `Polymorphic.type` through the nullability migration. Existing databases are not changed by editing an already-run migration; inspect `tags_tagged.fk_id` and apply an application migration if an earlier installation used the wrong type or signedness.

## Namespace identity

Tags with the same slug in different namespaces remain separate. Editing a namespaced tag preserves its prefix, and AND finders count distinct matched field values. Existing databases may contain links created through the earlier slug-only lookup; inspect those links before relying on namespace isolation.

Subclasses overriding `_tagExists()` or `_tagsExistBatch()` retain those hooks for the default unnamespaced mode. Namespace-aware custom lookups should override `_tagsExistByNamespace()` and use the protected `tagIdentity()` key.
