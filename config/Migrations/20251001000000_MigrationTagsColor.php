<?php

use Migrations\BaseMigration;

class MigrationTagsColor extends BaseMigration {

	/**
	 * @return void
	 */
	public function change(): void {
		$table = $this->table('tags_tags');
		$table->addColumn('color', 'string', [
			'default' => null,
			'limit' => 7,
			'null' => true,
		]);

		$table->update();
	}

}
