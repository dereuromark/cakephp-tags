<?php

use Cake\Core\Configure;
use Migrations\BaseMigration;

class MigrationTagsInit extends BaseMigration {

	/**
	 * Change Method.
	 *
	 * Write your reversible migrations using this method.
	 *
	 * More information on writing migrations is available here:
	 * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
	 *
	 * The following commands can be used in this method and Phinx will
	 * automatically reverse them when rolling back:
	 *
	 *    createTable
	 *    renameTable
	 *    addColumn
	 *    renameColumn
	 *    addIndex
	 *    addForeignKey
	 *
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 *
	 * @return void
	 */
	public function change() {
		// tags_tagged.tag_id (the plugin's own tags.id) and fk_id (the host record)
		// reference primary keys, so they follow the application's primary-key
		// signedness. The flag is false (signed) when unset, so an unset flag yields
		// signed columns matching the default-signed ids they reference. Unsigned only on MySQL.
		$type = (string)Configure::read('Polymorphic.type', 'integer');
		$signed = !(bool)Configure::read('Migrations.unsigned_primary_keys', false);

		$polymorphicOptions = [
			'default' => null,
			'null' => true,
		];
		if (in_array($type, ['integer', 'biginteger'], true)) {
			$polymorphicOptions['signed'] = $signed;
		}

		$table = $this->table('tags_tags');
		$table->addColumn('namespace', 'string', [
			'default' => null,
			'limit' => 255,
			'null' => true,
		]);
		$table->addColumn('slug', 'string', [
			'default' => null,
			'limit' => 255,
			'null' => false,
		]);
		$table->addColumn('label', 'string', [
			'default' => null,
			'limit' => 255,
			'null' => false,
		]);
		$table->addColumn('counter', 'integer', [
			'default' => 0,
			'length' => 11,
			'null' => false,
			'signed' => false,
		]);
		$table->addColumn('created', 'datetime', [
			'default' => null,
			'null' => true,
		]);
		$table->addColumn('modified', 'datetime', [
			'default' => null,
			'null' => true,
		]);
		$table->create();

		$table = $this->table('tags_tagged');
		$table->addColumn('tag_id', 'integer', [
			'default' => null,
			'length' => 11,
			'null' => true,
			'signed' => $signed,
		]);
		$table->addColumn('fk_id', $type, $polymorphicOptions);
		$table->addColumn('fk_table', 'string', [
			'default' => null,
			'limit' => 255,
			'null' => false,
		]);
		$table->addColumn('created', 'datetime', [
			'default' => null,
			'null' => true,
		]);
		$table->addColumn('modified', 'datetime', [
			'default' => null,
			'null' => true,
		]);
		$table->create();

		$table = $this->table('tags_tags');
		$table->addIndex(['slug', 'namespace'], ['unique' => true]);
		$table->update();

		$table = $this->table('tags_tagged');
		$table->addIndex(['tag_id', 'fk_id', 'fk_table'], ['unique' => true]);
		$table->update();
	}

}
