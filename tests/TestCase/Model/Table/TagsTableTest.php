<?php
namespace Tags\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class TagsTableTest extends TestCase {

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
	 * Test initialize method
	 *
	 * @return void
	 */
	public function test() {
		$result = $this->Tags->find()
			->all()
			->count();

		$this->assertSame(2, $result);

		$result = $this->Tags->find()->where(['slug' => 'color'])->first();
		$this->assertSame('Color', $result->label);
	}

}
