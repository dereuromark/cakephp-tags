<?php
namespace Tags\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Tags\Model\Table\TaggedTable;

class TaggedTableTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.tags.tags',
		'plugin.tags.tagged',
	];

	/**
	 * @var \Tags\Model\Table\TaggedTable
	 */
	protected $Tagged;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Tagged = TableRegistry::get('Tags.Tagged', ['table' => 'tags_tagged']);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Tagged);
		TableRegistry::clear();
	}

	/**
	 * Tries to auto-sort by tag alias if contained.
	 *
	 * @return void
	 */
	public function testFindSort() {
		$data = [
			'fk_id' => 1,
			'fk_model' => 'Muffins',
			'tag' => [
				'label' => 'Awesome',
				'slug' => 'awesome',
			],
		];
		$tagged = $this->Tagged->newEntity($data);
		$this->Tagged->save($tagged);

		$result = $this->Tagged->find()
			->contain('Tags')
			->where(['fk_id' => 1, 'fk_model' => 'Muffins'])
			->all()
			->toArray();

		$tags = Hash::extract($result, '{n}.tag.label');
		$ids = Hash::extract($result, '{n}.tag_id');

		$this->assertSame(['Awesome', 'Color', 'Dark Color'], $tags);
		$this->assertSame([3, 1, 2], $ids);
	}

	/**
	 * Fallback to unsorted by tag alias then.
	 *
	 * @return void
	 */
	public function testFindSortWithoutContain() {
		$data = [
			'fk_id' => 1,
			'fk_model' => 'Muffins',
			'tag' => [
				'label' => 'Awesome',
				'slug' => 'awesome',
			],
		];
		$tagged = $this->Tagged->newEntity($data);
		$this->Tagged->save($tagged);

		$result = $this->Tagged->find()
			->where(['fk_id' => 1, 'fk_model' => 'Muffins'])
			->all()
			->toArray();

		$ids = Hash::extract($result, '{n}.tag_id');
		$this->assertSame([1, 2, 3], $ids);
	}

	/**
	 * @return void
	 */
	public function testFindMatching() {
		$result = $this->Tagged->find()
			->matching('Tags', function ($q) {
				return $q->where(['label' => 'Dark Color']);
			})
			->all()
			->count();

		$this->assertSame(2, $result);
	}

	/**
	 * @return void
	 */
	public function testFindCloud() {
		$result = $this->Tagged->find('cloud')->toArray();

		$this->assertSame([20.0, 10.0], Hash::extract($result, '{n}.weight'));
	}

	/**
	 * @return void
	 */
	public function testCalculateWeights() {
		$entities = $this->Tagged->find()->all()->toArray();
		foreach ($entities as $key => $entity) {
			$entities[$key]->counter = mt_rand(1, 10);
		}

		$result = TaggedTable::calculateWeights($entities);

		foreach ($result as $entity) {
			$this->assertNotEmpty($entity->weight);
		}
	}

	/**
	 * @return void
	 */
	public function testCalculateWeightsEmpty() {
		$result = TaggedTable::calculateWeights([]);
		$this->assertSame([], $result);
	}

}
