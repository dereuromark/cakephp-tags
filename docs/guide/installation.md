# Installation

Composer checks the supported PHP and CakePHP versions for your application:

```sh
composer require dereuromark/cakephp-tags
```

The plugin database tables are created by [CakePHP Migrations](https://book.cakephp.org/migrations/5/en/). If your application does not have it:

```sh
composer require cakephp/migrations
```

Load both plugins in `src/Application.php`, preserving your existing plugin setup:

```php
$this->addPlugin('Migrations');
$this->addPlugin('Tags');
```

Tags loads its admin routes by default. To use only the behavior and helpers:

```php
$this->addPlugin('Tags', ['routes' => false]);
```

Run the plugin migrations against your configured database:

```sh
bin/cake migrations migrate -p Tags
```

This creates `tags_tags` and `tags_tagged`, including the color field. Your existing tagged table needs no extra column when you use `taggedCounter => false`, as in [getting started](./getting-started.md).

To cache each record's tag count, add an integer `tag_count` column with default `0` to the host table and enable the default counter configuration. Backfill existing records after introducing the cache; adding a zero-filled column does not calculate their existing counts.

## UUID host records

Before the first Tags migration, merge this key into the application configuration read by both HTTP and the migrations command:

```php
'Polymorphic' => [
    'type' => 'uuid',
],
```

`Polymorphic.type` controls `tags_tagged.fk_id`, which stores the host record ID. Supported choices include `integer`, `biginteger`, `uuid` and `binaryuuid`. Integer signedness follows `Migrations.unsigned_primary_keys`. Existing installations need an application migration to change the column type.

The plugin's own tag IDs remain integers. Changing those IDs to UUIDs requires matching schema changes to `tags_tags.id` and `tags_tagged.tag_id`, plus application overrides for admin methods that accept integer tag IDs.
