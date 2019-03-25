<?php
namespace Tags\Test\TestCase\Model\Table;

use Cake\Core\Configure;
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
	public $fixtures = [
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
	public function setUp() {
		parent::setUp();
		$this->Tags = TableRegistry::get('Tags.Tags');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Tags);
		TableRegistry::clear();
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
		$slugBehavior = ['Tools.Slugged' => ['mode' => [Text::class, 'slug']]];
		Configure::write('Tags.slugBehavior', $slugBehavior);
		TableRegistry::clear();

		$this->Tags = TableRegistry::get('Tags.Tags');

		$tag = $this->Tags->newEntity([
			'label' => 'Föö Bää',
		]);

		$this->Tags->saveOrFail($tag);
		$this->assertSame('Foo-Baa', $tag->slug);
	}

	/**
	 * @return void
	 */
	public function testMultipleTagsPerModel() {
		TableRegistry::clear();

		$table = TableRegistry::get('MultiTagsRecords');

		$entity = $table->newEntity([
			'name' => 'Föö Bää',
			'one_list' => 'x,y',
			'two_list' => '12, 66, 98',
		]);
		$one = Hash::extract($entity->one, '{n}.label');
		$this->assertSame(['x', 'y'], $one);

		$two = Hash::extract($entity->two, '{n}.label');
		$this->assertSame(['12', '66', '98'], $two);

		$table->saveOrFail($entity);

		$entity = $table->get($entity->id, ['contain' => ['TagsOne', 'TagsTwo']]);

		$this->assertSame('x, y', $entity->one_list);
		$this->assertSame('12, 66, 98', $entity->two_list);

		$this->assertSame(2, $entity->one_count);
		$this->assertSame(3, $entity->two_count);

		$untagged = $table->find('untaggedOne')->count();
		$this->assertSame(2, $untagged);
		$tagged = $table->find('taggedOne', ['tag' => 'x'])->first();
		$this->assertSame($entity->id, $tagged->id);

		$untagged = $table->find('untaggedTwo')->count();
		$this->assertSame(2, $untagged);
		$tagged = $table->find('taggedTwo', ['tag' => '66'])->first();
		$this->assertSame($entity->id, $tagged->id);
	}

}
