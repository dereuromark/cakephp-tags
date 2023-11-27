<?php

namespace Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class TagsFixture extends TestFixture {

	/**
	 * @var string
	 */
	public string $table = 'tags_tags';

	/**
	 * @var array
	 */
	public array $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'autoIncrement' => true],
		'namespace' => ['type' => 'string', 'length' => 255, 'null' => true],
		'slug' => ['type' => 'string', 'length' => 255],
		'label' => ['type' => 'string', 'length' => 255],
		'counter' => ['type' => 'integer', 'unsigned' => true, 'default' => '0', 'null' => false],
		'created' => ['type' => 'datetime', 'null' => false],
		'modified' => ['type' => 'datetime', 'null' => false],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']],
			'slug' => ['type' => 'unique', 'columns' => ['slug', 'namespace'], 'length' => []],
		],
		'_options' => [
			'collation' => 'utf8_unicode_ci',
		],
	];

	/**
	 * @var array
	 */
	public array $records = [
		[
			'namespace' => null,
			'slug' => 'color',
			'label' => 'Color',
			'counter' => 3,
			'modified' => '2011-07-15 19:47:38',
			'created' => '2011-07-15 19:47:38',
		],
		[
			'namespace' => null,
			'slug' => 'dark-color',
			'label' => 'Dark Color',
			'counter' => 2,
			'modified' => '2011-07-15 19:48:30',
			'created' => '2011-07-15 19:48:30',
		],
	];

}
