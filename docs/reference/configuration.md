# Configuration

Merge global settings under `Tags` in the application's configuration. The behavior and Tag helper read these defaults when constructed; per-instance options take precedence.

| Key | Default | Effect |
| --- | --- | --- |
| `field` | `tag_list` | Form field containing labels |
| `strategy` | `string` | String or array form output |
| `delimiter` | `,` | String input delimiter |
| `namespace` | `null` | Stored default namespace |
| `separator` | `null` | Namespace prefix separator |
| `finderField` | `null` | Finder uses `slug` unless overridden |
| `andSeparator` | `+` | AND tag filter separator |
| `orSeparator` | `,` | OR tag filter separator |
| `tagsCounter` | `['counter']` | Tag counter-cache configuration |
| `taggedCounter` | `['tag_count']` | Host counter fields; `false` disables |
| `fkModelAlias` | `null` | Host alias used as the discriminator |
| `fkModelField` | `fk_model` | Discriminator column |
| `tagsAlias` | `Tags` | Tag association name |
| `taggedAlias` | `Tagged` | Pivot association name |
| `inlineColorEditing` | `false` | Parse a trailing `@color` |
| `colorMap` | `null` | Replacement named-color map |
| `slug` | `null` | Custom label-to-slug callable |

`tagsAssoc` and `taggedAssoc` configure the ORM associations. See [multiple collections](../guide/behavior.md#multiple-collections) for coordinated aliases and properties. Changes to schema field names need matching table overrides; configuration alone does not rewrite the bundled tables and migrations.

Admin keys are `standalone`, `accessCheck`, `adminLayout`, `adminBackUrl` and `adminBackLabel`. See [admin access and layout](../guide/admin.md).

`Polymorphic.type` and `Migrations.unsigned_primary_keys` are separate application keys used during initial migrations. See [installation](../guide/installation.md#uuid-host-records).

There is no `slugBehavior` option. Use `slug` for custom generation. A literal `taggedCounter => true` is not a field configuration; use a field name, list of names or `false`.
