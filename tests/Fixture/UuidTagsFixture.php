<?php

namespace Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UuidTagsFixture extends TestFixture {

	/**
	 * @var string
	 */
	public string $table = 'uuid_tags';

	/**
	 * @var array
	 */
	public array $fields = [
		'id' => ['type' => 'uuid', 'length' => 36, 'null' => false],
		'namespace' => ['type' => 'string', 'length' => 255, 'null' => true],
		'slug' => ['type' => 'string', 'length' => 255],
		'label' => ['type' => 'string', 'length' => 255],
		'color' => ['type' => 'string', 'length' => 7, 'null' => true],
		'counter' => ['type' => 'integer', 'unsigned' => true, 'default' => '0', 'null' => false],
		'created' => ['type' => 'datetime', 'null' => true],
		'modified' => ['type' => 'datetime', 'null' => true],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']],
			'uuid-slug' => ['type' => 'unique', 'columns' => ['slug', 'namespace'], 'length' => []],
		],
	];

	/**
	 * @var array
	 */
	public array $records = [
		/*
		[
			'namespace' => null,
			'slug' => 'color',
			'label' => 'Color',
			'counter' => 3,
		],
		[
			'namespace' => null,
			'slug' => 'dark-color',
			'label' => 'Dark Color',
			'counter' => 2,
		],
		*/
	];

}
