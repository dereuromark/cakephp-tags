<?php
namespace Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CounterlessMuffinsFixture extends TestFixture {

	/**
	 * @var string
	 */
	public $table = 'tags_counterless_muffins';

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
			'name' => 'blue',
		],
		[
			'name' => 'red',
		],
	];

}
