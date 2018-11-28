<?php
namespace Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UuidTaggedFixture extends TestFixture {

	/**
	 * @var string
	 */
	public $table = 'tags_tagged';

	/**
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'uuid', 'length' => 36, 'null' => false],
		'tag_id' => ['type' => 'uuid', 'length' => 36, 'null' => false],
		'fk_id' => ['type' => 'uuid', 'length' => 36, 'null' => false],
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
	];

}
