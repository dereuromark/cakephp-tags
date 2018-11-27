<?php
namespace Tags\Test\TestCase\Model\Behavior;

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
		parent::setUp();
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		TableRegistry::clear();
	}

	/**
	 * @return void
	 */
	public function testUuids() {
		$table = TableRegistry::get('Posts', ['className' => 'Tags.UuidPosts', 'table' => 'uuid_posts']);

		$tagsTable = TableRegistry::get('Tags', ['className' => 'Tags.UuidTags', 'table' => 'uuid_tags']);
		$taggedTable = TableRegistry::get('Tagged', ['className' => 'Tags.UuidTagged', 'table' => 'uuid_tagged']);

		$table->addBehavior('Tags.Tag', [
			'taggedCounter' => false,
			'tagsAssoc' => [
				'className' => 'Tags.UuidTags',
			],
			'taggedAssoc' => [
				'className' => 'Tags.UuidTagged',
			],
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
		$table->patchEntity($record, ['tag_list' => 'Foo, Bar']);
		$table->saveOrFail($record);

		$savedRecord = $table->get($record->id, ['contain' => ['Tags']]);
		debug($savedRecord);

		$tagged = $taggedTable->find()->all()->toArray();
		debug($tagged);

		$tags = $tagsTable->find()->all()->toArray();
		debug($tags);
		$this->assertNotEmpty($tags);
	}

}
