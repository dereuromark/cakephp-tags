<?php

namespace Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UuidPostsFixture extends TestFixture {

	/**
	 * @var string
	 */
	public string $table = 'uuid_posts';

	/**
	 * @var array
	 */
	public array $fields = [
		'id' => ['type' => 'uuid', 'length' => 36, 'null' => false],
		'name' => ['type' => 'string', 'length' => 255],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']],
		],
	];

	/**
	 * @var array
	 */
	public array $records = [
		/*
		[
			'name' => 'blue',
		],
		[
			'name' => 'red',
		],
		*/
	];

}
