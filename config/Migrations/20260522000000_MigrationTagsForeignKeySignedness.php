<?php

use Cake\Core\Configure;
use Migrations\BaseMigration;

class MigrationTagsForeignKeySignedness extends BaseMigration {

	/**
	 * The `tags_tagged.tag_id` and `fk_id` columns reference primary keys
	 * (the plugin's own `tags.id` and any host record). They must therefore use
	 * the same signedness as the application's primary keys, which is governed by
	 * the `Migrations.unsigned_primary_keys` flag. The original migration left
	 * them at the default (signed), which mismatches unsigned-primary-key apps.
	 * Signedness only takes effect on MySQL; SQLite/Postgres ignore it.
	 *
	 * @return void
	 */
	public function up(): void {
		$signed = !(bool)Configure::read('Migrations.unsigned_primary_keys');

		$this->table('tags_tagged')
			->changeColumn('tag_id', 'integer', [
				'default' => null,
				'length' => 11,
				'null' => false,
				'signed' => $signed,
			])
			->changeColumn('fk_id', 'integer', [
				'default' => null,
				'length' => 11,
				'null' => false,
				'signed' => $signed,
			])
			->update();
	}

	/**
	 * @return void
	 */
	public function down(): void {
		$this->table('tags_tagged')
			->changeColumn('tag_id', 'integer', [
				'default' => null,
				'length' => 11,
				'null' => false,
				'signed' => true,
			])
			->changeColumn('fk_id', 'integer', [
				'default' => null,
				'length' => 11,
				'null' => false,
				'signed' => true,
			])
			->update();
	}

}
