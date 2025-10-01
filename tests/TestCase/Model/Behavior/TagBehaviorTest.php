<?php

namespace Tags\Test\TestCase\Model\Behavior;

use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use RuntimeException;

class TagBehaviorTest extends TestCase {

	/**
	 * @var array
	 */
	protected array $fixtures = [
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
	public function setUp(): void {
		parent::setUp();

		$table = TableRegistry::getTableLocator()->get('Tags.Muffins');
		$table->addBehavior('Tags.Tag');

		$this->Table = $table;
		$this->Behavior = $table->behaviors()->Tag;
	}

	/**
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		//TableRegistry::clear();
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
	public function testSaveAndReuse() {
		$data = [
			'name' => 'New',
			'tag_list' => 'Shiny Thing, Awesome',
		];
		$entity = $this->Table->newEntity($data);

		$this->Table->saveOrFail($entity);

		$taggedRows = $this->Table->Tagged->find()->contain('Tags')->where(['fk_id' => $entity->id])->all()->toArray();
		$tags = Hash::extract($taggedRows, '{n}.tag.label');
		$this->assertSame(['Awesome', 'Shiny Thing'], $tags);
		$slugs = Hash::extract($taggedRows, '{n}.tag.slug');
		$this->assertSame(['awesome', 'shiny-thing'], $slugs);

		$this->assertSame($this->Table->getAlias(), $taggedRows[0]['fk_model']);

		// Re-adding with same tags reuses existing tags
		$data = [
			'name' => 'Another',
			'tag_list' => 'Shiny Thing, Awesome',
		];
		$entity = $this->Table->newEntity($data);

		/** @var \Tags\Model\Entity\Tag $tag */
		foreach ($entity->tags as $tag) {
			$this->assertFalse($tag->isNew());
		}
		$this->Table->saveOrFail($entity);
	}

	/**
	 * Tests that whole entity guarded false by default will still work.
	 *
	 * @return void
	 */
	public function testSaveWithGuarding() {
		$data = [
			'name' => 'New',
			'tag_list' => 'Shiny, Awesome',
		];

		$entity = $this->Table->newEmptyEntity();
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
			'name' => 'Duplicate Tags!',
			'tag_list' => 'Color, Dark Color, Color',
		]);
		$this->Table->saveOrFail($entity);

		$Tags = $this->Table->Tagged->Tags;
		$count = $Tags->find()->where(['label' => 'Color'])->count();
		$this->assertEquals(1, $count);
		$count = $Tags->find()->where(['label' => 'Dark Color'])->count();
		$this->assertEquals(1, $count);
	}

	/**
	 * @return void
	 */
	public function testSavingDuplicatesCaseInsensitive() {
		$entity = $this->Table->newEntity([
			'name' => 'Duplicate Tags!',
			'tag_list' => 'Color, Dark Color, color',
		]);
		$this->Table->saveOrFail($entity);

		$count = $this->Table->Tagged->Tags->find()->where(['label' => 'Color'])->count();
		$this->assertSame(1, $count);
		$count = $this->Table->Tagged->Tags->find()->where(['label' => 'Dark Color'])->count();
		$this->assertSame(1, $count);
		$count = $this->Table->Tagged->Tags->find()->where(['label' => 'color'])->count();

		$config = ConnectionManager::getConfig('test');
		$expected = $config['driver'] === Mysql::class ? 1 : 0;
		$this->assertSame($expected, $count);
	}

	/**
	 * @return void
	 */
	public function testSaveManyDuplicates() {
		$entities = [
			$this->Table->newEntity([
				'name' => 'Duplicate Tags!',
				'tag_list' => 'Color, Dark Color',
			]),
			$this->Table->newEntity([
				'name' => 'Duplicate Tags 2!',
				'tag_list' => 'Dark Color, Light Color',
			]),
			$this->Table->newEntity([
				'name' => 'Duplicate Tags 3!',
				'tag_list' => 'Light Color, New Color',
			]),
		];
		$result = $this->Table->saveMany($entities);
		$this->assertTrue((bool)$result);

		$Tags = $this->Table->Tagged->Tags;

		$count = $Tags->find()->where(['label' => 'Color'])->count();
		$this->assertEquals(1, $count);
		$count = $Tags->find()->where(['label' => 'Dark Color'])->count();
		$this->assertEquals(1, $count);
		$count = $Tags->find()->where(['label' => 'Light Color'])->count();
		$this->assertEquals(1, $count);
		$count = $Tags->find()->where(['label' => 'New Color'])->count();
		$this->assertEquals(1, $count);
	}

	/**
	 * @return void
	 */
	public function testSavingExisting() {
		$entity = $this->Table->newEntity([
			'name' => 'Duplicate Tags?',
			'tag_list' => 'Color, Dark Color',
		]);
		$this->Table->saveOrFail($entity);

		$Tags = $this->Table->Tagged->Tags;
		$count = $Tags->find()->where(['label' => 'Color'])->count();
		$this->assertEquals(1, $count);
		$count = $Tags->find()->where(['label' => 'Dark Color'])->count();
		$this->assertEquals(1, $count);
	}

	/**
	 * @return void
	 */
	public function testSavingWithWrongKey() {
		$this->expectException(RuntimeException::class);

		$this->Table->newEntity([
			'name' => 'Duplicate Tags?',
			'tags' => 'X, Y',
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
					'fk_model' => 'Muffins',
				],
				'label' => 'foo',
				'slug' => 'foo',
				'namespace' => null,
				'color' => null,
			],
			1 => [
				'_joinData' => [
					'fk_model' => 'Muffins',
				],
				'slug' => '3-foobar',
				'label' => '3:foobar',
				'namespace' => null,
				'color' => null,
			],
			2 => [
				'_joinData' => [
					'fk_model' => 'Muffins',
				],
				'label' => 'bar',
				'slug' => 'bar',
				'namespace' => null,
				'color' => null,
			],
		];
		$this->assertEquals($expected, $result);

		$result = $this->Behavior->normalizeTags(['foo', 'bar']);
		$expected = [
			0 => [
				'_joinData' => [
					'fk_model' => 'Muffins',
				],
				'label' => 'foo',
				'slug' => 'foo',
				'namespace' => null,
				'color' => null,
			],
			1 => [
				'_joinData' => [
					'fk_model' => 'Muffins',
				],
				'label' => 'bar',
				'slug' => 'bar',
				'namespace' => null,
				'color' => null,
			],
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
				'namespace' => null,
				'color' => null,
			],
		];
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testNormalizeTagsNamespaced() {
		$this->Behavior->setConfig('separator', ':');
		$result = $this->Behavior->normalizeTags('foo, 3:foobar, foo:bar');
		$expected = [
			0 => [
				'_joinData' => [
					'fk_model' => 'Muffins',
				],
				'label' => 'foo',
				'slug' => 'foo',
				'namespace' => null,
				'color' => null,
			],
			1 => [
				'_joinData' => [
					'fk_model' => 'Muffins',
				],
				'slug' => 'foobar',
				'label' => 'foobar',
				'namespace' => '3',
				'color' => null,
			],
			2 => [
				'_joinData' => [
					'fk_model' => 'Muffins',
				],
				'label' => 'bar',
				'slug' => 'bar',
				'namespace' => 'foo',
				'color' => null,
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
			'tags' => [
				'_ids' => [
					'1',
					'2',
				],
			],
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
		$this->assertSame(3, $counter);

		$entity = $this->Table->newEntity($data);

		$this->Table->saveOrFail($entity);
		$entity = $this->Table->get($entity->id, ...['contain' => 'Tags']);
		$this->assertCount(2, $entity->tags);

		$this->Table->patchEntity($entity, ['tag_list' => '']);
		$this->Table->saveOrFail($entity);

		$entity = $this->Table->get($entity->id, ...['contain' => 'Tags']);
		$this->assertCount(0, $entity->tags);
	}

	/**
	 * @return void
	 */
	public function testCounterCacheDisabled() {
		$this->Table->removeBehavior('Tag');
		$this->Table->Tagged->removeBehavior('CounterCache');

		$this->Table->addBehavior('Tags.Tag', [
			'taggedCounter' => false,
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
	 * @return void
	 */
	public function testCounterCacheFieldException() {
		$table = TableRegistry::getTableLocator()->get('Tags.Buns', ['table' => 'buns']);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Field "non_existent" does not exist in table "buns"');

		$table->addBehavior('Tags.Tag', [
			'taggedCounter' => [
				'non_existent' => [],
			],
		]);
	}

	/**
	 * @return void
	 */
	public function testAssociationConditionsAreWorkingAsExpected() {
		$this->assertEquals(2, count($this->Table->get(1, ...['contain' => ['Tags']])->tags));
	}

	/**
	 * @return void
	 */
	public function testSaveWithSlugger() {
		$this->Table->removeBehavior('Tag');

		$this->Table->addBehavior('Tags.Tag', [
			'slug' => function ($tag) {
				return Text::slug($tag);
			},
		]);

		$data = [
			'name' => 'Muffin',
			'tag_list' => 'Foo Bar',
		];
		$entity = $this->Table->newEntity($data);
		$result = $this->Table->save($entity);

		$this->assertSame('Foo-Bar', $result->tags[0]->slug);
	}

	/**
	 * @return void
	 */
	public function testFinderTagged() {
		$result = $this->Table->Tags->find('all')->where(['slug' => 'color'])->distinct()->toArray();
		$this->assertCount(1, $result);

		$tag = [
			'label' => 'x',
		];
		$tag = $this->Table->Tags->newEntity($tag);
		$this->Table->Tags->save($tag);

		$result = $this->Table->Tagged->find('all')->where(['tag_id IN' => Hash::extract($result, '{n}.id')])->toArray();
		$this->assertCount(2, $result);

		$result = $this->Table->find('tagged', ...['slug' => 'color'])->orderByAsc($this->Table->aliasField('name'))->toArray();

		$expected = ['Blue', 'Red'];
		$this->assertSame($expected, Hash::extract($result, '{n}.name'));
	}

	/**
	 * @return void
	 */
	public function testFinderTaggedWithOr() {
		$entity = $this->Table->newEntity([
			'name' => 'Heavy',
			'tag_list' => 'color,weight',
		]);
		$this->Table->saveOrFail($entity);

		$result = $this->Table->Tags->find('all')->where(['slug IN' => ['color', 'weight']])->distinct()->toArray();
		$this->assertCount(2, $result);

		$result = $this->Table->Tagged->find('all')->where(['tag_id IN' => Hash::extract($result, '{n}.id')])->toArray();
		$this->assertCount(4, $result);

		$result = $this->Table->find('tagged', ...['slug' => 'color,weight'])->orderByAsc($this->Table->aliasField('name'))->toArray();
		$this->assertCount(3, $result);

		$expected = ['Blue', 'Heavy', 'Red'];
		$this->assertSame($expected, Hash::extract($result, '{n}.name'));
	}

	/**
	 * @return void
	 */
	public function testFinderTaggedWithAnd() {
		$entity = $this->Table->newEntity([
			'name' => 'Heavy',
			'tag_list' => 'color,weight',
		]);
		$this->Table->saveOrFail($entity);

		$result = $this->Table->Tags->find('all')->where(['slug IN' => ['color', 'weight']])->distinct()->toArray();
		$this->assertCount(2, $result);

		$result = $this->Table->Tagged->find('all')->where(['tag_id IN' => Hash::extract($result, '{n}.id')])->toArray();
		$this->assertCount(4, $result);

		$connectionConfig = $this->Table->getConnection()->config();
		$this->skipIf($connectionConfig['driver'] === Postgres::class, 'Only for MySQL/Sqlite for now');

		$result = $this->Table->find('tagged', ...['slug' => 'color+weight'])->orderByAsc($this->Table->aliasField('name'))->toArray();

		$expected = ['Heavy'];
		$this->assertSame($expected, Hash::extract($result, '{n}.name'));
	}

	/**
	 * @return void
	 */
	public function testFinderTaggedLabel() {
		$this->Table->behaviors()->Tag->setConfig('finderField', 'label');

		$tag = [
			'label' => 'x',
		];
		$tag = $this->Table->Tags->newEntity($tag);
		$this->Table->Tags->save($tag);

		$result = $this->Table->find('tagged', ...['label' => 'Color'])->orderByAsc($this->Table->aliasField('name'))->toArray();

		$expected = ['Blue', 'Red'];
		$this->assertSame($expected, Hash::extract($result, '{n}.name'));
	}

	/**
	 * @return void
	 */
	public function testFinderTaggedArray() {
		$entity = $this->Table->newEntity([
			'name' => 'Shiny',
			'tag_list' => 'Color,Beautiful',
		]);
		$this->Table->saveOrFail($entity);

		$tag = [
			'label' => 'x',
		];
		$tag = $this->Table->Tags->newEntity($tag);
		$this->Table->Tags->save($tag);

		$result = $this->Table->find('tagged', ...['slug' => ['color', 'beautiful']])->orderByAsc($this->Table->aliasField('name'))->toArray();

		$expected = ['Blue', 'Red', 'Shiny'];
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
	 * Tests that _normalizeTag returns proper named array keys.
	 *
	 * @return void
	 */
	public function testNormalizeTagWithNamedKeys() {
		$this->Behavior->setConfig('separator', ':');
		$this->Behavior->setConfig('inlineColorEditing', true);

		$result = $this->Behavior->normalizeTags('foo:bar@red');
		$this->assertCount(1, $result);
		$this->assertSame('bar', $result[0]['label']);
		$this->assertSame('foo', $result[0]['namespace']);
		$this->assertSame('#FF0000', $result[0]['color']);

		$result = $this->Behavior->normalizeTags('simple@blue');
		$this->assertCount(1, $result);
		$this->assertSame('simple', $result[0]['label']);
		$this->assertNull($result[0]['namespace']);
		$this->assertSame('#0000FF', $result[0]['color']);
	}

	/**
	 * Tests inline color editing with various color formats.
	 *
	 * @return void
	 */
	public function testInlineColorEditing() {
		$this->Behavior->setConfig('inlineColorEditing', true);

		// Named color
		$result = $this->Behavior->normalizeTags('MyTag@green');
		$this->assertCount(1, $result);
		$this->assertSame('MyTag', $result[0]['label']);
		$this->assertSame('#00FF00', $result[0]['color']);

		// Hex color with hash
		$result = $this->Behavior->normalizeTags('AnotherTag@#FF5733');
		$this->assertCount(1, $result);
		$this->assertSame('AnotherTag', $result[0]['label']);
		$this->assertSame('#FF5733', $result[0]['color']);

		// Hex color without hash
		$result = $this->Behavior->normalizeTags('ThirdTag@1A2B3C');
		$this->assertCount(1, $result);
		$this->assertSame('ThirdTag', $result[0]['label']);
		$this->assertSame('#1A2B3C', $result[0]['color']);

		// Invalid color - @ is still parsed but color is null
		$result = $this->Behavior->normalizeTags('Tag@invalidcolor');
		$this->assertCount(1, $result);
		$this->assertSame('Tag', $result[0]['label']);
		$this->assertNull($result[0]['color']);

		// Multiple tags with colors
		$result = $this->Behavior->normalizeTags('Tag1@red, Tag2@blue, Tag3');
		$this->assertCount(3, $result);
		$this->assertSame('Tag1', $result[0]['label']);
		$this->assertSame('#FF0000', $result[0]['color']);
		$this->assertSame('Tag2', $result[1]['label']);
		$this->assertSame('#0000FF', $result[1]['color']);
		$this->assertSame('Tag3', $result[2]['label']);
		$this->assertNull($result[2]['color']);
	}

	/**
	 * Tests inline color editing with namespace separator.
	 *
	 * @return void
	 */
	public function testInlineColorEditingWithNamespace() {
		$this->Behavior->setConfig('separator', ':');
		$this->Behavior->setConfig('inlineColorEditing', true);

		// Namespace + label + color
		$result = $this->Behavior->normalizeTags('category:item@purple');
		$this->assertCount(1, $result);
		$this->assertSame('item', $result[0]['label']);
		$this->assertSame('category', $result[0]['namespace']);
		$this->assertSame('#800080', $result[0]['color']);

		// Multiple tags with namespace and color
		$result = $this->Behavior->normalizeTags('ns1:tag1@orange, ns2:tag2@pink');
		$this->assertCount(2, $result);
		$this->assertSame('tag1', $result[0]['label']);
		$this->assertSame('ns1', $result[0]['namespace']);
		$this->assertSame('#FFA500', $result[0]['color']);
		$this->assertSame('tag2', $result[1]['label']);
		$this->assertSame('ns2', $result[1]['namespace']);
		$this->assertSame('#FFC0CB', $result[1]['color']);
	}

	/**
	 * Tests custom color map configuration.
	 *
	 * @return void
	 */
	public function testCustomColorMap() {
		$this->Behavior->setConfig('inlineColorEditing', true);
		$this->Behavior->setConfig('colorMap', [
			'primary' => '#007BFF',
			'secondary' => '#6C757D',
			'success' => '#28A745',
			'danger' => '#DC3545',
			'warning' => '#FFC107',
			'info' => '#17A2B8',
		]);

		// Custom color name
		$result = $this->Behavior->normalizeTags('Tag@primary');
		$this->assertCount(1, $result);
		$this->assertSame('Tag', $result[0]['label']);
		$this->assertSame('#007BFF', $result[0]['color']);

		// Multiple custom colors
		$result = $this->Behavior->normalizeTags('Important@danger, Safe@success');
		$this->assertCount(2, $result);
		$this->assertSame('Important', $result[0]['label']);
		$this->assertSame('#DC3545', $result[0]['color']);
		$this->assertSame('Safe', $result[1]['label']);
		$this->assertSame('#28A745', $result[1]['color']);

		// Default color name should not work with custom map
		$result = $this->Behavior->normalizeTags('Tag@red');
		$this->assertCount(1, $result);
		$this->assertSame('Tag', $result[0]['label']);
		$this->assertNull($result[0]['color']);

		// Hex colors still work
		$result = $this->Behavior->normalizeTags('Tag@#FF5733');
		$this->assertCount(1, $result);
		$this->assertSame('Tag', $result[0]['label']);
		$this->assertSame('#FF5733', $result[0]['color']);
	}

	/**
	 * Tests that inline color editing updates existing tags' colors.
	 *
	 * @return void
	 */
	public function testInlineColorEditingUpdatesExistingTags() {
		$this->Behavior->setConfig('inlineColorEditing', true);

		// Create a tag without color
		$entity = $this->Table->newEntity([
			'name' => 'First',
			'tag_list' => 'Updateable',
		]);
		$this->Table->saveOrFail($entity);

		$tag = $this->Table->Tags->find()->where(['slug' => 'updateable'])->first();
		$this->assertNotNull($tag);
		$this->assertNull($tag->color);
		$originalId = $tag->id;

		// Use the same tag with a color - should update the existing tag
		$entity2 = $this->Table->newEntity([
			'name' => 'Second',
			'tag_list' => 'Updateable@blue',
		]);
		$this->Table->saveOrFail($entity2);

		// Reload the tag from database using the Tags table directly
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tag = $tagsTable->get($originalId);
		$this->assertSame('#0000FF', $tag->color);

		// Verify no duplicate was created
		$count = $tagsTable->find()->where(['slug' => 'updateable'])->count();
		$this->assertSame(1, $count, 'Should have exactly 1 tag with slug "updateable"');
	}

	/**
	 * @return void
	 */
	public function testFinderUntaggedWithoutCounterField() {
		$table = TableRegistry::getTableLocator()->get('Tags.CounterlessMuffins');

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
