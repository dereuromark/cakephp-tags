# Helper and assets

Load the helper in `AppView::initialize()` or in the template:

```php
$this->loadHelper('Tags.Tag');
```

## Form controls

Inside your existing entity form:

```php
<?= $this->Tag->control() ?>
```

The default is a text input for `tag_list`. For the array strategy, supply available labels as both option keys and labels:

```php
<?= $this->Tag->control([
    'strategy' => 'array',
    'options' => ['CakePHP' => 'CakePHP', 'Tutorials' => 'Tutorials'],
]) ?>
```

Configure the behavior with the same strategy. Without explicit options, the array control offers the entity's currently selected labels. A library such as Select2 is optional and must be installed and initialized by the application.

For another collection, pass its `field`:

```php
<?= $this->Tag->control(['field' => 'topic_list']) ?>
```

The helper merges global `Tags` configuration with per-call options. Keep admin closures and behavior-only configuration out of a helper's HTML options; use a normal FormHelper control when sharing a broad global configuration.

## Colored labels

```php
<?= $this->Tag->tag($tag) ?>
<?= $this->Tag->tags($article->tags) ?>
<?= $this->Tag->badges($article->tags) ?>
<?= $this->Tag->pills($article->tags) ?>
```

Tags use their stored color or `#cccccc`, with a calculated black or white text color. Badges and pills use Bootstrap class names; provide the matching CSS if you want Bootstrap styling.

`tag()` accepts `url`, `class`, `style`, `labelKey`, `colorKey`, `defaultColor` and `escape`. Labels are escaped by default. `tags()` adds a wrapper; set `wrapper => false` to return the tags alone.

`list()` can append each slug to a base URL:

```php
<?= $this->Tag->list($article->tags, [
    'url' => ['plugin' => null, 'controller' => 'Articles', 'action' => 'tagged'],
    'separator' => ', ',
]) ?>
```

The application must implement the target action and apply its permissions. The plugin does not create a public tag browsing controller.

## Color utilities

`colorControl()` renders a FormHelper color input. `getContrastColor()` and `adjustBrightness()` operate on six-digit hex colors. For validation, normalization and conversions, use `Tags\Utility\ColorUtility`:

```php
use Tags\Utility\ColorUtility;

$valid = ColorUtility::isValidHex('#FF5733');
$normalized = ColorUtility::normalize('ff5733');
$rgb = ColorUtility::hexToRgb('#FF5733');
$hex = ColorUtility::rgbToHex(255, 87, 51);
```

An icon is application data: add your own schema field and render it using your chosen icon library. There is no built-in icon field or automatic icon rendering.
