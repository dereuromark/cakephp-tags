<?php

use Migrations\BaseMigration;

class MigrationTagsNulls extends BaseMigration {

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
		$table = $this->table('tags_tagged');
		$table->changeColumn('tag_id', 'integer', [
			'default' => null,
			'length' => 11,
			'null' => false,
		]);
		$table->changeColumn('fk_id', 'integer', [
			'default' => null,
			'length' => 11,
			'null' => false,
		]);
		$table->changeColumn('created', 'datetime', [
			'default' => null,
			'null' => false,
		]);
		$table->changeColumn('modified', 'datetime', [
			'default' => null,
			'null' => false,
		]);

		$table->update();
	}

}
