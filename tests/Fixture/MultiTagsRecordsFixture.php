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
		],
		[
			'name' => 'round',
		],
	];

}
