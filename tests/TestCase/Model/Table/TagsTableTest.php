<?php
namespace Muffin\Tags\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class TagsTableTest extends TestCase
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

	/** @var \Muffin\Tags\Model\Table\TagsTable */
	protected $Tags;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();
		$this->Tags = TableRegistry::get('Muffin/Tags.Tags');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown()
	{
		parent::tearDown();
		unset($this->Tags);
		TableRegistry::clear();
	}

	/**
	 * Test initialize method
	 *
	 * @return void
	 */
	public function test()
	{
		$result = $this->Tags->find()
			->all()
			->count();

		$this->assertSame(2, $result);

		$result = $this->Tags->find()->where(['slug'=> 'color'])->first();
		$this->assertSame('Color', $result->label);
	}
}
