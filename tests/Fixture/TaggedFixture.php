<?php
namespace Muffin\Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class TaggedFixture extends TestFixture
{
    public $table = 'tags_tagged';

    public $fields = [
        'id' => ['type' => 'integer', 'length' => 10, 'autoIncrement' => true],
        'tag_id' => ['type' => 'integer', 'null' => false],
        'fk_id' => ['type' => 'integer', 'null' => false],
        'fk_table' => ['type' => 'string', 'limit' => 255, 'null' => false],
        'created' => ['type' => 'datetime', 'null' => true],
        'modified' => ['type' => 'datetime', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
			'unique' => ['type' => 'unique', 'columns' => ['tag_id', 'fk_id', 'fk_table'], 'length' => []],
        ],
    ];

    public $records = [
        [
            'tag_id' => 1,
            'fk_id' => 1,
            'fk_table' => 'Muffins',
        ],
        [
            'tag_id' => 2,
            'fk_id' => 1,
            'fk_table' => 'Muffins',
        ],
        [
            'tag_id' => 1,
            'fk_id' => 2,
            'fk_table' => 'Muffins',
        ],
        [
            'tag_id' => 1,
            'fk_id' => 1,
            'fk_table' => 'Buns',
        ],
        [
            'tag_id' => 2,
            'fk_id' => 2,
            'fk_table' => 'Buns',
        ],
    ];

    public function init()
    {
        $created = $modified = date('Y-m-d H:i:s');
        array_walk($this->records, function (&$record) use ($created, $modified) {
            $record += compact('created', 'modified');
        });
        parent::init();
    }
}
