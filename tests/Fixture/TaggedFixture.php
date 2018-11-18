<?php
namespace Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class TaggedFixture extends TestFixture {

	/**
	 * @var string
	 */
	public $table = 'tags_tagged';

	/**
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'autoIncrement' => true],
		'tag_id' => ['type' => 'integer', 'null' => false],
		'fk_id' => ['type' => 'integer', 'null' => false],
		'fk_model' => ['type' => 'string', 'limit' => 255, 'null' => false],
		'created' => ['type' => 'datetime', 'null' => false],
		'modified' => ['type' => 'datetime', 'null' => false],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']],
			'tag_id' => ['type' => 'unique', 'columns' => ['tag_id', 'fk_id', 'fk_model'], 'length' => []],
		],
	];

	/**
	 * @var array
	 */
	public $records = [
		[
			'tag_id' => 1,
			'fk_id' => 1,
			'fk_model' => 'Muffins',
		],
		[
			'tag_id' => 2,
			'fk_id' => 1,
			'fk_model' => 'Muffins',
		],
		[
			'tag_id' => 1,
			'fk_id' => 2,
			'fk_model' => 'Muffins',
		],
		[
			'tag_id' => 1,
			'fk_id' => 1,
			'fk_model' => 'Buns',
		],
		[
			'tag_id' => 2,
			'fk_id' => 2,
			'fk_model' => 'Buns',
		],
	];

	/**
	 * @return void
	 */
	public function init() {
		$created = $modified = date('Y-m-d H:i:s');
		array_walk($this->records, function (&$record) use ($created, $modified) {
			$record += compact('created', 'modified');
		});
		parent::init();
	}

}
