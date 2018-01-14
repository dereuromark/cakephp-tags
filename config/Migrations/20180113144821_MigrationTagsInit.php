<?php

use Phinx\Migration\AbstractMigration;

class MigrationTagsInit extends AbstractMigration {

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
		]);
		$table->addColumn('fk_id', 'integer', [
			'default' => null,
			'length' => 11,
			'null' => true,
		]);
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
