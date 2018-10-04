<?php
namespace Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BunsFixture extends TestFixture {

	/**
	 * @var string
	 */
	public $table = 'tags_buns';

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
			'name' => 'square',
			'tag_count' => 1,
		],
		[
			'name' => 'round',
			'tag_count' => 1,
		],
	];

}
