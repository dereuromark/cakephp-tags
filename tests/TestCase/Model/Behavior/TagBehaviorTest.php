<?php
namespace Muffin\Tags\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Muffin\Tags\Model\Behavior\TagBehavior;

class TagBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.Muffin/Tags.Buns',
        'plugin.Muffin/Tags.Muffins',
        'plugin.Muffin/Tags.Tagged',
        'plugin.Muffin/Tags.Tags',
    ];

	/**
	 * @var \Cake\ORM\Table
	 */
    protected $Table;

	/**
	 * @var TagBehavior
	 */
    protected $Behavior;

	/**
	 * @return void
	 */
    public function setUp()
    {
        parent::setUp();

        $table = TableRegistry::get('Muffin/Tags.Muffins', ['table' => 'tags_muffins']);
        $table->addBehavior('Muffin/Tags.Tag');

        $this->Table = $table;
        $this->Behavior = $table->behaviors()->Tag;
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Behavior);
    }

    public function testSavingDuplicates()
    {
        $entity = $this->Table->newEntity([
            'name' => 'Duplicate Tags?',
            'tags' => 'Color, Dark Color'
        ]);
        $this->Table->save($entity);
        $Tags = $this->Table->Tagged->Tags;
        $count = $Tags->find()->where(['label' => 'Color'])->count();
        $this->assertEquals(1, $count);
        $count = $Tags->find()->where(['label' => 'Dark Color'])->count();
        $this->assertEquals(1, $count);
    }

    public function testDefaultInitialize()
    {
        $belongsToMany = $this->Table->association('Tags');
        $this->assertInstanceOf('Cake\ORM\Association\BelongsToMany', $belongsToMany);

        $hasMany = $this->Table->association('Tagged');
        $this->AssertInstanceOf('Cake\ORM\Association\HasMany', $hasMany);
    }

    public function testCustomInitialize()
    {
        $this->Table->removeBehavior('Tag');
        $this->Table->addBehavior('Muffin/Tags.Tag', [
            'tagsAlias' => 'Labels',
            'taggedAlias' => 'Labelled',
        ]);

        $belongsToMany = $this->Table->association('Labels');
        $this->assertInstanceOf('Cake\ORM\Association\BelongsToMany', $belongsToMany);

        $hasMany = $this->Table->association('Labelled');
        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $hasMany);
    }

    public function testNormalizeTags()
    {
        $result = $this->Behavior->normalizeTags('foo, 3:foobar, bar');
        $expected = [
            0 => [
                '_joinData' => [
                    'fk_table' => 'Muffins'
                ],
                'label' => 'foo',
                'slug' => 'foo'
            ],
            1 => [
                '_joinData' => [
                    'fk_table' => 'Muffins'
                ],
                //'namespace' => '3',
                'slug' => '3-foobar',
				'label' => '3:foobar',
            ],
            2 => [
                '_joinData' => [
                    'fk_table' => 'Muffins'
                ],
                'label' => 'bar',
                'slug' => 'bar'
            ]
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Behavior->normalizeTags(['foo', 'bar']);
        $expected = [
            0 => [
                '_joinData' => [
                    'fk_table' => 'Muffins'
                ],
                'label' => 'foo',
                'slug' => 'foo'
            ],
            1 => [
                '_joinData' => [
                    'fk_table' => 'Muffins'
                ],
                'label' => 'bar',
                'slug' => 'bar'
            ]
        ];

        $this->assertEquals($expected, $result);

        $result = $this->Behavior->normalizeTags('first, ');
        $expected = [
            [
                '_joinData' => [
                    'fk_table' => 'Muffins',
                ],
                'label' => 'first',
                'slug' => 'first',
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testMarshalingOnlyNewTags()
    {
        $data = [
            'name' => 'Muffin',
            'tag_list' => 'foo, bar',
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->dirty('tags'));

        $data = [
            'name' => 'Muffin',
            'tag_list' => [
                'foo',
                'bar',
            ],
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->dirty('tags'));
    }

    public function testMarshalingOnlyExistingTags()
    {
        $data = [
            'name' => 'Muffin',
            'tag_list' => '1:Color, 2:Dark Color',
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->dirty('tags'));

        $data = [
            'name' => 'Muffin',
            'tags' => ['_ids' => [
                '1',
                '2',
            ]],
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->dirty('tags'));
    }

    public function testMarshalingBothNewAndExistingTags()
    {
        $data = [
            'name' => 'Muffin',
            'tag_list' => '1:Color, foo',
        ];

        $entity = $this->Table->newEntity($data);

        $this->assertEquals(2, count($entity->get('tags')));
        $this->assertTrue($entity->dirty('tags'));
    }

    public function testMarshalingWithEmptyTagsString()
    {
        $data = [
            'name' => 'Muffin',
            'tag_list' => '',
        ];

        $entity = $this->Table->newEntity($data);
        $this->assertEquals(0, count($entity->get('tags')));
    }

    public function testSaveIncrementsCounter()
    {
        $data = [
            'name' => 'Muffin',
            'tag_list' => 'Color, Dark Color',
        ];

        $counter = $this->Table->Tags->get(1)->counter;
        $entity = $this->Table->newEntity($data);

        $this->Table->saveOrFail($entity);

        $result = $this->Table->Tags->get(1)->counter;
        $expected = $counter + 1;
        $this->assertEquals($expected, $result);

        $result = $this->Table->get($entity->id)->tag_count;
        $expected = 2;
        $this->assertEquals($expected, $result);
    }

    public function testCounterCacheDisabled()
    {
        $this->Table->removeBehavior('Tag');
        $this->Table->Tagged->removeBehavior('CounterCache');

        $this->Table->addBehavior('Muffin/Tags.Tag', [
            'taggedCounter' => false
        ]);

        $count = $this->Table->get(1)->tag_count;

        $data = [
            'id' => 1,
            'tag_list' => '1:Color, 2:Dark Color, new color',
        ];

        $entity = $this->Table->newEntity($data);
        $this->Table->save($entity);

        $result = $this->Table->get(1)->tag_count;
        $this->assertEquals($count, $result);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Field "non_existent" does not exist in table "tags_buns"
     */
    public function testCounterCacheFieldException()
    {
        $table = TableRegistry::get('Muffin/Tags.Buns', ['table' => 'tags_buns']);
        $table->addBehavior('Muffin/Tags.Tag', [
            'taggedCounter' => [
                'non_existent' => []
            ]
        ]);
    }

	/**
	 * @return void
	 */
    public function testAssociationConditionsAreWorkingAsExpected()
    {
        $this->assertEquals(2, count($this->Table->get(1, ['contain' => ['Tags']])->tags));
    }

	/**
	 * This works fine on MySQL and other case insensitive DBs.
	 * For Postgres make sure the slugger returns a lower-cased version!
	 *
	 * @return void
	 */
    public function testSaveWithSlug() {
		$tag = [
			'label' => 'X Y',
		];
		$tag = $this->Table->Tags->newEntity($tag);
		$result = $this->Table->Tags->save($tag);
		$this->assertSame('X-Y', $result->slug);
	}

	/**
	 * @return void
	 */
    public function testFinder() {
		$result = $this->Table->Tags->find('all')->where(['slug' => 'color'])->distinct()->toArray();
		//FIXME: should not be duplicated
		$this->assertCount(1, $result);

		$tag = [
			'label' => 'x',
		];
		$tag = $this->Table->Tags->newEntity($tag);
		$this->Table->Tags->save($tag);

		$result = $this->Table->Tagged->find('all')->where(['tag_id IN' => Hash::extract($result, '{n}.id')])->toArray();
		$this->assertCount(2, $result);

		$result = $this->Table->find('tagged', ['tag' => 'color'])->toArray();

		$expected = ['blue', 'red'];
		$this->assertSame($expected, Hash::extract($result, '{n}.name'));
	}
}
