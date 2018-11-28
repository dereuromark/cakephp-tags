<?php
namespace Tags\Test\TestCase\Model\Behavior;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

class UuidTest extends TestCase {

	/**
	 * @var array
	 */
	public $fixtures = [
		'plugin.Tags.UuidTags',
		'plugin.Tags.UuidTagged',
		'plugin.Tags.UuidPosts',
	];

	/**
	 * @return void
	 */
	public function setUp() {
		Cache::clearAll();

		parent::setUp();
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		TableRegistry::clear();

		Cache::clearAll();
	}

	/**
	 * @return void
	 */
	public function testUuids() {
		$table = TableRegistry::get('Posts', ['table' => 'tags_posts']);

		$table->addBehavior('Tags.Tag', [
			'taggedCounter' => false,
		]);

		$record = [
			'name' => 'TestMe',
		];
		$record = $table->newEntity($record);
		$table->saveOrFail($record);

		$result = $table->find('untagged')->toArray();
		$expected = ['TestMe'];
		$this->assertSame($expected, Hash::extract($result, '{n}.name'));

		$record = $table->find()->where(['name' => 'TestMe'])->firstOrFail();
		$table->patchEntity($record, ['tag_list' => 'Foo, Bar'], ['fields' => ['tag_list', 'tags']]);
		$table->saveOrFail($record);

		$savedRecord = $table->get($record->id, ['contain' => ['Tags']]);

		$tagged = $table->Tagged->find()->all()->toArray();
		$this->assertCount(2, $tagged);

		$this->assertCount(2, $savedRecord->tags);

		$tags = $table->Tags->find()->orderAsc('slug')->all()->toArray();
		$expected = ['Bar', 'Foo'];
		$this->assertSame($expected, Hash::extract($tags, '{n}.slug'));
	}

}
