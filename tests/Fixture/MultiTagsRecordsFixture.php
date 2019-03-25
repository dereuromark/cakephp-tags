<?php
namespace Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class MultiTagsRecordsFixture extends TestFixture {

	/**
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'integer', 'length' => 10, 'autoIncrement' => true],
		'name' => ['type' => 'string', 'length' => 255],
		'one_count' => ['type' => 'integer', 'null' => false, 'default' => 0],
		'two_count' => ['type' => 'integer', 'null' => false, 'default' => 0],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']],
		],
	];

	/**
	 * @var array
	 */
	public $records = [
		[
			'name' => 'square',
			'one_count' => 0,
			'two_count' => 0,
		],
		[
			'name' => 'round',
			'one_count' => 0,
			'two_count' => 0,
		],
	];

}
