# Installation

Composer checks the supported PHP and CakePHP versions for your application:

```sh
composer require dereuromark/cakephp-tags
```

The plugin database tables are created by [CakePHP Migrations](https://book.cakephp.org/migrations/5/en/). If your application does not have it:

```sh
composer require cakephp/migrations
```

Load the plugins in `src/Application.php`, preserving your existing setup. If Migrations is already registered in `config/plugins.php`, keep that registration and add only Tags:

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

## Optional host counter

For an existing Articles table, create an application migration such as `config/Migrations/20260101000000_AddArticleTagCount.php`:

```php
<?php
use Migrations\BaseMigration;

class AddArticleTagCount extends BaseMigration {
    public function change(): void {
        $this->table('articles')
            ->addColumn('tag_count', 'integer', ['default' => 0, 'null' => false])
            ->update();
    }
}
```

Run the application's migration command without `-p Tags`, then enable the behavior's default counter. For an existing installation, a one-time application command can backfill counts through the model-scoped association:

```php
$articles = $this->fetchTable('Articles');
foreach ($articles->find()->select(['id']) as $article) {
    $count = $articles->Tagged->find()->where(['Tagged.fk_id' => $article->id])->count();
    $articles->updateAll(['tag_count' => $count], ['id' => $article->id]);
}
```

Adapt the primary-key and counter names to your table. Coordinate this backfill with writes so a concurrent save cannot be overwritten by an earlier count.

## UUID host records

Before the first Tags migration, merge this key into the application configuration read by both HTTP and the migrations command:

```php
'Polymorphic' => [
    'type' => 'uuid',
],
```

`Polymorphic.type` controls `tags_tagged.fk_id`, which stores the host record ID. Supported choices include `integer`, `biginteger`, `uuid` and `binaryuuid`. Integer signedness follows `Migrations.unsigned_primary_keys`. Existing installations need an application migration to change the column type.

The plugin's own tag IDs remain integers. Changing those IDs to UUIDs requires matching schema changes to `tags_tags.id` and `tags_tagged.tag_id`, plus application overrides for admin methods that accept integer tag IDs.
