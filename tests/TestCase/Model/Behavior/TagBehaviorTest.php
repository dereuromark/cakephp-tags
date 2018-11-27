<?php
namespace Tags\Test\TestCase\Model\Behavior;

use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

class TagBehaviorTest extends TestCase {

	/**
	 * @var array
	 */
	public $fixtures = [
		'plugin.Tags.Tags',
		'plugin.Tags.Tagged',
		'plugin.Tags.Buns',
		'plugin.Tags.Muffins',
		'plugin.Tags.CounterlessMuffins',
	];

	/**
	 * @var \Cake\ORM\Table
	 */
	protected $Table;

	/**
	 * @var \Tags\Model\Behavior\TagBehavior
	 */
	protected $Behavior;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$table = TableRegistry::get('Tags.Muffins', ['table' => 'tags_muffins']);
		$table->addBehavior('Tags.Tag');

		$this->Table = $table;
		$this->Behavior = $table->behaviors()->Tag;
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		TableRegistry::clear();
		unset($this->Behavior);
	}

	/**
	 * @return void
	 */
	public function testFind() {
		$entity = $this->Table->find()->contain('Tags')->first();
		$this->assertCount(2, $entity->tags);
		$this->assertSame('Color, Dark Color', $entity->tag_list);
	}

	/**
	 * @return void
	 */
	public function testFindHydrateFalse() {
		$entity = $this->Table->find()->contain('Tags')->enableHydration(false)->first();
		$this->assertCount(2, $entity['tags']);
		$this->assertSame('Color, Dark Color', $entity['tag_list']);
	}

	/**
	 * @return void
	 */
	public function testSave() {
		$data = [
			'name' => 'New',
			'tag_list' => 'Shiny, Awesome'
		];
		$entity = $this->Table->newEntity($data);
		$this->Table->saveOrFail($entity);

		$taggedRows = $this->Table->Tagged->find()->contain('Tags')->where(['fk_id' => $entity->id])->all()->toArray();
		$tags = Hash::extract($taggedRows, '{n}.tag.label');
		$this->assertSame(['Awesome', 'Shiny'], $tags);

		$this->assertSame($this->Table->getAlias(), $taggedRows[0]['fk_model']);
	}

	/**
	 * Tests that whole entity guarded false by default will still work.
	 *
	 * @return void
	 */
	public function testSaveWithGuarding() {
		$data = [
			'name' => 'New',
			'tag_list' => 'Shiny, Awesome'
		];

		$entity = $this->Table->newEntity();
		$entity->setAccess('tags', false);

		$entity = $this->Table->patchEntity($entity, $data);
		$this->assertNotEmpty($entity->tags);

		$this->Table->saveOrFail($entity);

		$taggedRows = $this->Table->Tagged->find()->contain('Tags')->where(['fk_id' => $entity->id])->all()->toArray();
		$tags = Hash::extract($taggedRows, '{n}.tag.label');
		$this->assertSame(['Awesome', 'Shiny'], $tags);
	}

	/**
	 * @return void
	 */
	public function testSavingDuplicates() {
		$entity = $this->Table->newEntity([
			'name' => 'Duplicate Tags?',
			'tag_list' => 'Color, Dark Color'
		]);
		$this->Table->saveOrFail($entity);

		$Tags = $this->Table->Tagged->Tags;
		$count = $Tags->find()->where(['label' => 'Color'])->count();
		$this->assertEquals(1, $count);
		$count = $Tags->find()->where(['label' => 'Dark Color'])->count();
		$this->assertEquals(1, $count);
	}

	/**
	 * @expectedException \RuntimeException
	 *
	 * @return void
	 */
	public function testSavingWithWrongKey() {
		$this->Table->newEntity([
			'name' => 'Duplicate Tags?',
			'tags' => 'X, Y'
		]);
	}

	/**
	 * @return void
	 */
	public function testDefaultInitialize() {
		$belongsToMany = $this->Table->getAssociation('Tags');
		$this->assertInstanceOf(BelongsToMany::class, $belongsToMany);

		$hasMany = $this->Table->getAssociation('Tagged');
		$this->AssertInstanceOf(HasMany::class, $hasMany);
	}

	/**
	 * @return void
	 */
	public function testCustomInitialize() {
		$this->Table->removeBehavior('Tag');
		$this->Table->addBehavior('Tags.Tag', [
			'tagsAlias' => 'Labels',
			'taggedAlias' => 'Labelled',
		]);

		$belongsToMany = $this->Table->getAssociation('Labels');
		$this->assertInstanceOf(BelongsToMany::class, $belongsToMany);

		$hasMany = $this->Table->getAssociation('Labelled');
		$this->assertInstanceOf(HasMany::class, $hasMany);
	}

	/**
	 * @return void
	 */
	public function testPrepareTagsForOutput() {
		$tags = [
			[
				'label' => 'Foo',
			],
			[
				'label' => 'Bar',
			],
		];

		$result = $this->Behavior->prepareTagsForOutput($tags);
		$this->assertSame('Foo, Bar', $result);

		$this->Behavior->setConfig('strategy', 'array');
		$result = $this->Behavior->prepareTagsForOutput($tags);
		$this->assertSame(['Foo', 'Bar'], $result);
	}

	/**
	 * @return void
	 */
	public function testNormalizeTags() {
		$result = $this->Behavior->normalizeTags('foo, 3:foobar, bar');
		$expected = [
			0 => [
				'_joinData' => [
					'fk_model' => 'Muffins'
				],
				'label' => 'foo',
				'slug' => 'foo'
			],
			1 => [
				'_joinData' => [
					'fk_model' => 'Muffins'
				],
				//'namespace' => '3',
				'slug' => '3-foobar',
				'label' => '3:foobar',
			],
			2 => [
				'_joinData' => [
					'fk_model' => 'Muffins'
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
					'fk_model' => 'Muffins'
				],
				'label' => 'foo',
				'slug' => 'foo'
			],
			1 => [
				'_joinData' => [
					'fk_model' => 'Muffins'
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
					'fk_model' => 'Muffins',
				],
				'label' => 'first',
				'slug' => 'first',
			],
		];
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testMarshalingOnlyNewTags() {
		$data = [
			'name' => 'Muffin',
			'tag_list' => 'foo, bar',
		];

		$entity = $this->Table->newEntity($data);

		$this->assertEquals(2, count($entity->get('tags')));
		$this->assertTrue($entity->isDirty('tags'));

		$data = [
			'name' => 'Muffin',
			'tag_list' => [
				'foo',
				'bar',
			],
		];

		$entity = $this->Table->newEntity($data);

		$this->assertEquals(2, count($entity->get('tags')));
		$this->assertTrue($entity->isDirty('tags'));
	}

	/**
	 * @return void
	 */
	public function testMarshalingOnlyExistingTags() {
		$data = [
			'name' => 'Muffin',
			'tag_list' => '1:Color, 2:Dark Color',
		];

		$entity = $this->Table->newEntity($data);

		$this->assertEquals(2, count($entity->get('tags')));
		$this->assertTrue($entity->isDirty('tags'));

		$data = [
			'name' => 'Muffin',
			'tags' => ['_ids' => [
				'1',
				'2',
			]],
		];

		$entity = $this->Table->newEntity($data);

		$this->assertEquals(2, count($entity->get('tags')));
		$this->assertTrue($entity->isDirty('tags'));
	}

	/**
	 * @return void
	 */
	public function testMarshalingBothNewAndExistingTags() {
		$data = [
			'name' => 'Muffin',
			'tag_list' => '1:Color, foo',
		];

		$entity = $this->Table->newEntity($data);

		$this->assertEquals(2, count($entity->get('tags')));
		$this->assertTrue($entity->isDirty('tags'));
	}

	/**
	 * @return void
	 */
	public function testMarshalingWithEmptyTagsString() {
		$data = [
			'name' => 'Muffin',
			'tag_list' => '',
		];

		$entity = $this->Table->newEntity($data);
		$this->assertSame([], $entity->get('tags'));
	}

	/**
	 * @return void
	 */
	public function testSaveIncrementsCounter() {
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

	/**
	 * @return void
	 */
	public function testSaveEmptyStringRemovesAllTags() {
		$data = [
			'name' => 'Muffin',
			'tag_list' => 'Color, Dark Color',
		];

		$counter = $this->Table->Tags->get(1)->counter;
		$entity = $this->Table->newEntity($data);

		$this->Table->saveOrFail($entity);
		$entity = $this->Table->get($entity->id, ['contain' => 'Tags']);
		$this->assertCount(2, $entity->tags);

		$this->Table->patchEntity($entity, ['tag_list' => '']);
		$this->Table->saveOrFail($entity);

		$entity = $this->Table->get($entity->id, ['contain' => 'Tags']);
		$this->assertCount(0, $entity->tags);
	}

	/**
	 * @return void
	 */
	public function testCounterCacheDisabled() {
		$this->Table->removeBehavior('Tag');
		$this->Table->Tagged->removeBehavior('CounterCache');

		$this->Table->addBehavior('Tags.Tag', [
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
	 * @return void
	 */
	public function testCounterCacheFieldException() {
		$table = TableRegistry::get('Tags.Buns', ['table' => 'tags_buns']);
		$table->addBehavior('Tags.Tag', [
			'taggedCounter' => [
				'non_existent' => []
			]
		]);
	}

	/**
	 * @return void
	 */
	public function testAssociationConditionsAreWorkingAsExpected() {
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
	public function testFinderTagged() {
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

	/**
	 * @return void
	 */
	public function testFinderUntagged() {
		$record = [
			'name' => 'TestMe',
		];
		$record = $this->Table->newEntity($record);
		$this->Table->saveOrFail($record);

		$record = $this->Table->get($record->id);
		$this->assertSame(0, $record->tag_count);

		$result = $this->Table->find('untagged')->toArray();
		$expected = ['TestMe'];
		$this->assertSame($expected, Hash::extract($result, '{n}.name'));
	}

	/**
	 * @return void
	 */
	public function testFinderUntaggedWithoutCounterField() {
		$table = TableRegistry::get('Tags.CounterlessMuffins', ['table' => 'tags_counterless_muffins']);

		$table->addBehavior('Tags.Tag', [
			'taggedCounter' => false,
		]);

		$record = [
			'name' => 'TestMe',
		];
		$record = $table->newEntity($record);
		$table->saveOrFail($record);

		$result = $table->find('untagged')->toArray();
		$expected = ['blue', 'red', 'TestMe'];
		$this->assertSame($expected, Hash::extract($result, '{n}.name'));

		$record = $table->find()->where(['name' => 'red'])->firstOrFail();
		$table->patchEntity($record, ['tag_list' => 'Foo, Bar']);
		$table->saveOrFail($record);

		$result = $table->find('untagged')->toArray();
		$expected = ['blue', 'TestMe'];
		$this->assertSame($expected, Hash::extract($result, '{n}.name'));
	}

}
