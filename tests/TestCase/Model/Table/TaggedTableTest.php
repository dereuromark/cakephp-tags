<?php
namespace Tags\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

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

}
