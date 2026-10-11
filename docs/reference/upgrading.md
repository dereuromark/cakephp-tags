# Upgrading

The active branch targets CakePHP 5. Composer declares the exact supported PHP and CakePHP ranges. This audit prepares the current implementation for future framework work; it does not declare CakePHP 6 support.

For the earlier major-version migration, retain the repository's [UPGRADE.md](https://github.com/dereuromark/cakephp-tags/blob/master/UPGRADE.md).

## Current fixes

- Colored tag spans escape their style attribute, matching the linked-tag path.
- List-based tag finders use the host table primary key and SQL alias, including when separate collections have custom model discriminators.
- Standalone admin mode bypasses host initialization and beforeFilter callbacks consistently.
- Host redirects and stopped events are preserved before the optional plugin gate runs.

Application callbacks and middleware still control access. Review the [admin configuration](../guide/admin.md) if you use standalone mode or the optional gate.

## Replace old examples

Use `fetchTable()` and the correct table variable in controller examples. Load the current Tags association before patching an existing entity. Use named finder arguments, such as `find('tagged', value: 'cakephp')`.

The default host counter needs a `tag_count` column. Disable it for the simplest setup. `TagsTable::recalculateCounters()` rebuilds global tag counts; it does not backfill every host model.

The supplied routes place the dashboard at `/admin/tags` and the Tags controller at `/admin/tags/tags`. Entity routes use current brace placeholders, such as `/tag/{slug}`, and require an application controller that implements the public action.
