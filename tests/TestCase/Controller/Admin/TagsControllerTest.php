<?php
declare(strict_types=1);

namespace Tags\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class TagsControllerTest extends TestCase {

	use IntegrationTestTrait;

	/**
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Tags.Tags',
	];

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->disableErrorHandlerMiddleware();
	}

	/**
	 * @return void
	 */
	public function testChangeNamespacePageUsesDistinctFormFields(): void {
		$this->get('/admin/tags/tags/change-namespace');

		$this->assertResponseOk();
		$this->assertResponseContains('name="to_namespace_select"');
		$this->assertResponseContains('name="to_namespace_new"');
		$this->assertResponseContains('?namespace=__none__');
	}

	/**
	 * @return void
	 */
	public function testChangeNamespaceMovesTagsToExistingNamespace(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'namespace' => 'palette',
			'slug' => 'accent',
			'label' => 'Accent',
		]));

		$this->post('/admin/tags/tags/change-namespace', [
			'from_namespace' => '__none__',
			'to_namespace_select' => 'palette',
			'to_namespace_new' => '',
		]);

		$this->assertRedirect('/admin/tags/tags/change-namespace');

		$result = $tagsTable->find()
			->where(['namespace' => 'palette'])
			->orderByAsc('slug')
			->all()
			->extract('slug')
			->toList();
		$this->assertSame(['accent', 'color', 'dark-color'], $result);
	}

	/**
	 * @return void
	 */
	public function testChangeNamespaceRejectsConflicts(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'namespace' => 'palette',
			'slug' => 'color',
			'label' => 'Color In Palette',
		]));

		$this->post('/admin/tags/tags/change-namespace', [
			'from_namespace' => '__none__',
			'to_namespace_select' => 'palette',
			'to_namespace_new' => '',
		]);

		$this->assertResponseOk();
		$this->assertNoRedirect();

		$result = $tagsTable->find()
			->where(['namespace IS' => null])
			->orderByAsc('slug')
			->all()
			->extract('slug')
			->toList();
		$this->assertSame(['color', 'dark-color'], $result);
	}

	/**
	 * @return void
	 */
	public function testAddAutoGeneratesSlugAndNormalizesBlankNamespace(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');

		$this->post('/admin/tags/tags/add', [
			'label' => 'Fresh Tag',
			'slug' => '',
			'namespace' => '',
			'color' => '#123456',
		]);

		$this->assertRedirect('/admin/tags/tags');

		$tag = $tagsTable->find()
			->where(['slug' => 'fresh-tag'])
			->firstOrFail();

		$this->assertNull($tag->namespace);
		$this->assertSame('#123456', $tag->color);
	}

}
