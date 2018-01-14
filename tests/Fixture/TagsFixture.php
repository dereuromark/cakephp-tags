<?php
namespace Muffin\Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class TagsFixture extends TestFixture
{
    public $table = 'tags_tags';

    public $fields = [
        'id' => ['type' => 'integer', 'length' => 10, 'autoIncrement' => true],
        'namespace' => ['type' => 'string', 'length' => 255, 'null' => true],
        'slug' => ['type' => 'string', 'length' => 255],
        'label' => ['type' => 'string', 'length' => 255],
        'counter' => ['type' => 'integer', 'unsigned' => true, 'default' => 0, 'null' => true],
        'created' => ['type' => 'datetime', 'null' => true],
        'modified' => ['type' => 'datetime', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
			'slug' => ['type' => 'unique', 'columns' => ['slug', 'namespace'], 'length' => []],
        ],
    ];

    public $records = [
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
    ];

}
