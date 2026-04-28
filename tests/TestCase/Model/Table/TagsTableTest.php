<?php

namespace Tags\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Tools\Utility\Text;

class TagsTableTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected array $fixtures = [
		'plugin.Tags.Tags',
		'plugin.Tags.Tagged',
		'plugin.Tags.MultiTagsRecords',
	];

	/**
	 * @var \Tags\Model\Table\TagsTable
	 */
	protected $Tags;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->Tags = TableRegistry::getTableLocator()->get('Tags.Tags');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		unset($this->Tags);
		//TableRegistry::clear();
	}

	/**
	 * @return void
	 */
	public function testFind() {
		$result = $this->Tags->find()
			->all()
			->count();

		$this->assertSame(2, $result);

		/** @var \Tags\Model\Entity\Tag $result */
		$result = $this->Tags->find()->where(['slug' => 'color'])->first();
		$this->assertSame('Color', $result->label);
	}

	/**
	 * @return void
	 */
	public function testCustomSluggerWithConfig() {
		$behaviors = ['Tools.Slugged' => ['mode' => [Text::class, 'slug']]];
		//TableRegistry::clear();

		$this->Tags = TableRegistry::getTableLocator()->get('Tags.Tags');
		$this->Tags->addBehaviors($behaviors);

		$tag = $this->Tags->newEntity([
			'label' => 'Föö Bää',
		]);

		$this->Tags->saveOrFail($tag);
		$this->assertSame('Foo-Baa', $tag->slug);
	}

	/**
	 * @return void
	 */
	public function testValidationColor() {
		$tag = $this->Tags->newEntity([
			'label' => 'Test Tag',
			'slug' => 'test-tag',
			'color' => '#FF5733',
		]);
		$this->assertEmpty($tag->getErrors());

		$tag = $this->Tags->newEntity([
			'label' => 'Test Tag',
			'slug' => 'test-tag',
			'color' => '#ff5733',
		]);
		$this->assertEmpty($tag->getErrors());

		$tag = $this->Tags->newEntity([
			'label' => 'Test Tag',
			'slug' => 'test-tag',
			'color' => 'invalid',
		]);
		$this->assertNotEmpty($tag->getErrors());
		$this->assertArrayHasKey('color', $tag->getErrors());

		$tag = $this->Tags->newEntity([
			'label' => 'Test Tag',
			'slug' => 'test-tag',
			'color' => '#GGGGGG',
		]);
		$this->assertNotEmpty($tag->getErrors());
		$this->assertArrayHasKey('color', $tag->getErrors());

		$tag = $this->Tags->newEntity([
			'label' => 'Test Tag',
			'slug' => 'test-tag',
			'color' => '',
		]);
		$this->assertEmpty($tag->getErrors());

		$tag = $this->Tags->newEntity([
			'label' => 'Test Tag',
			'slug' => 'test-tag',
		]);
		$this->assertEmpty($tag->getErrors());
	}

	/**
	 * @return void
	 */
	public function testBeforeMarshalAutoGeneratesSlugAndNormalizesNamespace(): void {
		$tag = $this->Tags->newEntity([
			'label' => 'Auto Slug Test',
			'slug' => '',
			'namespace' => '',
		]);

		$this->assertEmpty($tag->getErrors());
		$this->assertSame('auto-slug-test', $tag->slug);
		$this->assertNull($tag->namespace);
	}

	/**
	 * @return void
	 */
	public function testMoveNamespace(): void {
		$count = $this->Tags->moveNamespace(null, 'palette');

		$this->assertSame(2, $count);

		$result = $this->Tags->find()
			->where(['namespace' => 'palette'])
			->orderByAsc('slug')
			->all()
			->extract('slug')
			->toList();
		$this->assertSame(['color', 'dark-color'], $result);
	}

	/**
	 * @return void
	 */
	public function testMoveNamespaceWithConflicts(): void {
		$entity = $this->Tags->newEntity([
			'namespace' => 'palette',
			'slug' => 'color',
			'label' => 'Color In Palette',
		]);
		$this->Tags->saveOrFail($entity);

		$this->assertSame(1, $this->Tags->countNamespaceConflicts(null, 'palette'));

		$this->expectExceptionMessage('conflicting slug(s)');
		$this->Tags->moveNamespace(null, 'palette');
	}

	/**
	 * @return void
	 */
	public function testFindDuplicatesByPluralSuffix(): void {
		$this->Tags->saveOrFail($this->Tags->newEntity([
			'slug' => 'colors',
			'label' => 'Colors',
		]));

		$groups = $this->Tags->findDuplicates();

		$this->assertCount(1, $groups);
		$this->assertCount(2, $groups[0]);
		$slugs = array_map(fn ($t) => $t->slug, $groups[0]);
		sort($slugs);
		$this->assertSame(['color', 'colors'], $slugs);
	}

	/**
	 * @return void
	 */
	public function testFindDuplicatesByLevenshtein(): void {
		$this->Tags->saveOrFail($this->Tags->newEntity([
			'slug' => 'colur',
			'label' => 'Colur',
		]));

		$groups = $this->Tags->findDuplicates();
		$this->assertNotEmpty($groups);
	}

	/**
	 * @return void
	 */
	public function testFindDuplicatesIgnoresAcrossNamespaces(): void {
		$this->Tags->saveOrFail($this->Tags->newEntity([
			'namespace' => 'palette',
			'slug' => 'color',
			'label' => 'Color In Palette',
		]));

		$groups = $this->Tags->findDuplicates();
		$this->assertSame([], $groups);
	}

	/**
	 * @return void
	 */
	public function testFindDuplicatesNamespaceFilter(): void {
		$this->Tags->saveOrFail($this->Tags->newEntity([
			'namespace' => 'palette',
			'slug' => 'color',
			'label' => 'Color In Palette',
		]));
		$this->Tags->saveOrFail($this->Tags->newEntity([
			'namespace' => 'palette',
			'slug' => 'colors',
			'label' => 'Colors In Palette',
		]));

		$groups = $this->Tags->findDuplicates('palette');
		$this->assertCount(1, $groups);
		$this->assertCount(2, $groups[0]);
	}

	/**
	 * @return void
	 */
	public function testFindDuplicatesNoneWhenAllUnique(): void {
		$this->Tags->deleteAll([]);
		$this->Tags->saveOrFail($this->Tags->newEntity([
			'slug' => 'apple',
			'label' => 'Apple',
		]));
		$this->Tags->saveOrFail($this->Tags->newEntity([
			'slug' => 'zebra',
			'label' => 'Zebra',
		]));

		$this->assertSame([], $this->Tags->findDuplicates());
	}

	/**
	 * @return void
	 */
	public function testDeleteOrphaned(): void {
		$this->Tags->saveOrFail($this->Tags->newEntity([
			'slug' => 'orphan-1',
			'label' => 'Orphan 1',
		]));
		$this->Tags->saveOrFail($this->Tags->newEntity([
			'slug' => 'orphan-2',
			'label' => 'Orphan 2',
		]));

		$count = $this->Tags->deleteOrphaned();
		$this->assertSame(2, $count);
		$this->assertFalse($this->Tags->exists(['slug' => 'orphan-1']));
		$this->assertTrue($this->Tags->exists(['slug' => 'color']));
	}

	/**
	 * @return void
	 */
	public function testDeleteOrphanedWithNamespace(): void {
		$this->Tags->saveOrFail($this->Tags->newEntity([
			'namespace' => 'palette',
			'slug' => 'orphan-palette',
			'label' => 'Orphan Palette',
		]));
		$this->Tags->saveOrFail($this->Tags->newEntity([
			'slug' => 'orphan-global',
			'label' => 'Orphan Global',
		]));

		$count = $this->Tags->deleteOrphaned('palette');
		$this->assertSame(1, $count);
		$this->assertFalse($this->Tags->exists(['slug' => 'orphan-palette']));
		$this->assertTrue($this->Tags->exists(['slug' => 'orphan-global']));
	}

	/**
	 * @return void
	 */
	public function testRecalculateCounters(): void {
		$this->Tags->updateAll(['counter' => 99], ['id' => 1]);
		$this->Tags->updateAll(['counter' => 0], ['id' => 2]);

		$updated = $this->Tags->recalculateCounters();
		$this->assertSame(2, $updated);

		$this->assertSame(3, $this->Tags->get(1)->counter);
		$this->assertSame(2, $this->Tags->get(2)->counter);
	}

	/**
	 * @return void
	 */
	public function testRecalculateCountersWhenAlreadyCorrect(): void {
		$updated = $this->Tags->recalculateCounters();
		$this->assertSame(0, $updated);
	}

	/**
	 * @return void
	 */
	public function testMerge(): void {
		$taggedTable = TableRegistry::getTableLocator()->get('Tags.Tagged');

		$result = $this->Tags->merge(2, 1);
		$this->assertTrue($result);

		$this->assertFalse($this->Tags->exists(['id' => 2]));
		// Source had 2 associations (Muffins:1, Buns:2). One was a duplicate (Muffins:1 has both tags 1 and 2),
		// so the duplicate is deleted, the unique Buns:2 is moved to tag 1.
		// Original tag 1 had 3 associations + 1 moved = 4
		$tag1 = $this->Tags->get(1);
		$this->assertSame(4, $tag1->counter);
		$this->assertSame(0, $taggedTable->find()->where(['tag_id' => 2])->count());
	}

	/**
	 * @return void
	 */
	public function testMergeReturnsFalseOnNamespaceMismatch(): void {
		$other = $this->Tags->newEntity([
			'namespace' => 'palette',
			'slug' => 'palette-color',
			'label' => 'Palette Color',
		]);
		$this->Tags->saveOrFail($other);

		$result = $this->Tags->merge($other->id, 1);
		$this->assertFalse($result);

		$this->assertTrue($this->Tags->exists(['id' => $other->id]));
		$this->assertTrue($this->Tags->exists(['id' => 1]));
	}

	/**
	 * @return void
	 */
	public function testMultipleTagsPerModel() {
		//TableRegistry::clear();

		$table = TableRegistry::getTableLocator()->get('MultiTagsRecords');
		$entity = $table->newEntity([
			'name' => 'Föö Bää',
			'one_list' => 'x,y',
			'two_list' => '12, 66, 98',
		]);
		$this->assertNotEmpty($entity->one);
		$this->assertNotEmpty($entity->two);

		$one = Hash::extract($entity->one, '{n}.label');
		$this->assertSame(['x', 'y'], $one);

		$two = Hash::extract($entity->two, '{n}.label');
		$this->assertSame(['12', '66', '98'], $two);

		$table->saveOrFail($entity);

		$entity = $table->get($entity->id, ...['contain' => ['TagsOne', 'TagsTwo']]);

		$this->assertSame('x, y', $entity->one_list);
		$this->assertSame('12, 66, 98', $entity->two_list);

		$this->assertSame(2, $entity->one_count);
		$this->assertSame(3, $entity->two_count);

		$untagged = $table->find('untaggedOne')->count();
		$this->assertSame(2, $untagged);
		$tagged = $table->find('taggedOne', value: 'x')->first();
		$this->assertSame($entity->id, $tagged->id);

		$untagged = $table->find('untaggedTwo')->count();
		$this->assertSame(2, $untagged);
		$tagged = $table->find('taggedTwo', value: '66')->first();
		$this->assertSame($entity->id, $tagged->id);
	}

}
