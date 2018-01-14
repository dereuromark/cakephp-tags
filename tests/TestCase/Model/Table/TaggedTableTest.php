<?php
namespace Muffin\Tags\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class TaggedTableTest extends TestCase
{

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.muffin/tags.tags',
		'plugin.muffin/tags.tagged',
	];

	/** @var \Muffin\Tags\Model\Table\TaggedTable */
	protected $Tagged;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();
		$this->Tagged = TableRegistry::get('Muffin/Tags.Tagged', ['table' => 'tags_tagged']);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown()
	{
		parent::tearDown();
		unset($this->Tagged);
		TableRegistry::clear();
	}

	/**
	 * @return void
	 */
	public function testFindMatching()
	{
		$result = $this->Tagged->find()
			->matching('Tags', function ($q) {
				return $q->where(['label' => 'Dark Color']);
			})
			->all()
			->count();

		$this->assertSame(2, $result);
	}
}
