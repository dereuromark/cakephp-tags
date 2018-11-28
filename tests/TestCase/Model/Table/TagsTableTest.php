<?php
namespace Tags\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Tools\Utility\Text;

class TagsTableTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.tags.tags',
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

}
