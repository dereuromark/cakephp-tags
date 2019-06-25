<?php
namespace Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class MuffinsFixture extends TestFixture {

	/**
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'integer', 'length' => 10, 'autoIncrement' => true],
		'name' => ['type' => 'string', 'length' => 255],
		'tag_count' => ['type' => 'integer', 'null' => false, 'default' => 0],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']],
		],
	];

	/**
	 * @var array
	 */
	public $records = [
		[
			'name' => 'blue',
			'tag_count' => 2,
		],
		[
			'name' => 'red',
			'tag_count' => 1,
		],
	];

}
