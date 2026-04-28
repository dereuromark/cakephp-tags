<?php
declare(strict_types=1);

namespace Tags\Test\TestCase\Controller\Admin;

use Cake\Http\Exception\MethodNotAllowedException;
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
		'plugin.Tags.Tagged',
	];

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->disableErrorHandlerMiddleware();
		$this->enableRetainFlashMessages();
	}

	/**
	 * @return void
	 */
	public function testIndex(): void {
		$this->get('/admin/tags/tags');

		$this->assertResponseOk();
		$this->assertResponseContains('Color');
		$this->assertResponseContains('Dark Color');
	}

	/**
	 * @return void
	 */
	public function testIndexFilteredByNamespace(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'namespace' => 'palette',
			'slug' => 'accent',
			'label' => 'Accent',
		]));

		$this->get('/admin/tags/tags?namespace=palette');

		$this->assertResponseOk();
		$this->assertResponseContains('Accent');
		$this->assertResponseNotContains('Dark Color');
	}

	/**
	 * @return void
	 */
	public function testIndexFilteredByNoneNamespace(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'namespace' => 'palette',
			'slug' => 'accent',
			'label' => 'Accent Tag',
		]));

		$this->get('/admin/tags/tags?namespace=__none__');

		$this->assertResponseOk();
		$this->assertResponseContains('Color');
		$this->assertResponseNotContains('Accent Tag');
	}

	/**
	 * @return void
	 */
	public function testIndexFilteredByOrphaned(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'slug' => 'unused-orphan',
			'label' => 'Unused Orphan',
		]));

		$this->get('/admin/tags/tags?orphaned=1');

		$this->assertResponseOk();
		$this->assertResponseContains('Unused Orphan');
		$this->assertResponseNotContains('<strong>Color</strong>');
		$this->assertResponseNotContains('<strong>Dark Color</strong>');
	}

	/**
	 * @return void
	 */
	public function testIndexFilteredBySearch(): void {
		$this->get('/admin/tags/tags?search=dark');

		$this->assertResponseOk();
		$this->assertResponseContains('Dark Color');
		$this->assertResponseNotContains('<strong>Color</strong>');
	}

	/**
	 * @return void
	 */
	public function testView(): void {
		$this->get('/admin/tags/tags/view/1');

		$this->assertResponseOk();
		$this->assertResponseContains('Color');
		$this->assertResponseContains('Muffins');
		$this->assertResponseContains('Buns');
	}

	/**
	 * @return void
	 */
	public function testAddGet(): void {
		$this->get('/admin/tags/tags/add');

		$this->assertResponseOk();
		$this->assertResponseContains('Add Tag');
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
		$this->assertFlashMessage(__d('tags', 'The tag has been saved.'));

		$tag = $tagsTable->find()
			->where(['slug' => 'fresh-tag'])
			->firstOrFail();

		$this->assertNull($tag->namespace);
		$this->assertSame('#123456', $tag->color);
	}

	/**
	 * @return void
	 */
	public function testAddNormalizesNoneSentinel(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');

		$this->post('/admin/tags/tags/add', [
			'label' => 'Sentinel Tag',
			'slug' => 'sentinel-tag',
			'namespace' => '__none__',
		]);

		$this->assertRedirect('/admin/tags/tags');

		$tag = $tagsTable->find()
			->where(['slug' => 'sentinel-tag'])
			->firstOrFail();

		$this->assertNull($tag->namespace);
	}

	/**
	 * @return void
	 */
	public function testAddInvalid(): void {
		$this->post('/admin/tags/tags/add', [
			'label' => '',
			'slug' => '',
			'namespace' => '',
		]);

		$this->assertResponseOk();
		$this->assertNoRedirect();
		$this->assertFlashMessage(__d('tags', 'The tag could not be saved. Please try again.'));
	}

	/**
	 * @return void
	 */
	public function testEditGet(): void {
		$this->get('/admin/tags/tags/edit/1');

		$this->assertResponseOk();
		$this->assertResponseContains('Edit Tag');
		$this->assertResponseContains('Color');
	}

	/**
	 * @return void
	 */
	public function testEditPost(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');

		$this->post('/admin/tags/tags/edit/1', [
			'label' => 'Updated Color',
			'slug' => 'color',
			'namespace' => '',
			'color' => '#ABCDEF',
		]);

		$this->assertRedirect('/admin/tags/tags');
		$this->assertFlashMessage(__d('tags', 'The tag has been saved.'));

		$tag = $tagsTable->get(1);
		$this->assertSame('Updated Color', $tag->label);
		$this->assertSame('#ABCDEF', $tag->color);
	}

	/**
	 * @return void
	 */
	public function testEditPostInvalid(): void {
		$this->post('/admin/tags/tags/edit/1', [
			'label' => '',
			'slug' => '',
		]);

		$this->assertResponseOk();
		$this->assertNoRedirect();
		$this->assertFlashMessage(__d('tags', 'The tag could not be saved. Please try again.'));
	}

	/**
	 * @return void
	 */
	public function testDelete(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');

		$this->post('/admin/tags/tags/delete/1');

		$this->assertRedirect('/admin/tags/tags');
		$this->assertFlashMessage(__d('tags', 'The tag has been deleted.'));
		$this->assertFalse($tagsTable->exists(['id' => 1]));
	}

	/**
	 * @return void
	 */
	public function testDeleteWithGetIsRejected(): void {
		$this->expectException(MethodNotAllowedException::class);

		$this->get('/admin/tags/tags/delete/1');
	}

	/**
	 * @return void
	 */
	public function testMerge(): void {
		$this->get('/admin/tags/tags/merge');

		$this->assertResponseOk();
		$this->assertResponseContains('Merge Tags');
		$this->assertResponseContains('name="source"');
		$this->assertResponseContains('name="target"');
	}

	/**
	 * @return void
	 */
	public function testMergePreviewMissingSourceRedirects(): void {
		$this->get('/admin/tags/tags/merge-preview');

		$this->assertRedirect('/admin/tags/tags/merge');
		$this->assertFlashMessage(__d('tags', 'Please select both source and target tags.'));
	}

	/**
	 * @return void
	 */
	public function testMergePreviewSameSourceTargetRedirects(): void {
		$this->get('/admin/tags/tags/merge-preview?source=1&target=1');

		$this->assertRedirect('/admin/tags/tags/merge');
		$this->assertFlashMessage(__d('tags', 'Source and target tags must be different.'));
	}

	/**
	 * @return void
	 */
	public function testMergePreviewDifferentNamespacesRedirects(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$other = $tagsTable->newEntity([
			'namespace' => 'palette',
			'slug' => 'mauve',
			'label' => 'Mauve',
		]);
		$tagsTable->saveOrFail($other);

		$this->get('/admin/tags/tags/merge-preview?source=1&target=' . $other->id);

		$this->assertRedirect('/admin/tags/tags/merge');
		$this->assertFlashMessage(__d('tags', 'Tags must be in the same namespace to merge.'));
	}

	/**
	 * @return void
	 */
	public function testMergePreviewShowsImpact(): void {
		$this->get('/admin/tags/tags/merge-preview?source=1&target=2');

		$this->assertResponseOk();
		$this->assertResponseContains('Merge Preview');
		$this->assertResponseContains('Color');
		$this->assertResponseContains('Dark Color');
	}

	/**
	 * @return void
	 */
	public function testMergePreviewExecutesMerge(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$taggedTable = TableRegistry::getTableLocator()->get('Tags.Tagged');

		$this->post('/admin/tags/tags/merge-preview', [
			'source' => 1,
			'target' => 2,
			'confirm' => '1',
		]);

		$this->assertRedirect('/admin/tags/tags');
		$this->assertFalse($tagsTable->exists(['id' => 1]));
		$this->assertSame(0, $taggedTable->find()->where(['tag_id' => 1])->count());
	}

	/**
	 * @return void
	 */
	public function testDuplicates(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'slug' => 'colors',
			'label' => 'Colors',
		]));

		$this->get('/admin/tags/tags/duplicates');

		$this->assertResponseOk();
		$this->assertResponseContains('Potential Duplicates');
		$this->assertResponseContains('Color');
		$this->assertResponseContains('Colors');
	}

	/**
	 * @return void
	 */
	public function testDuplicatesEmpty(): void {
		$taggedTable = TableRegistry::getTableLocator()->get('Tags.Tagged');
		$taggedTable->deleteAll([]);
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->deleteAll([]);
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'slug' => 'unique-foo',
			'label' => 'Unique Foo',
		]));

		$this->get('/admin/tags/tags/duplicates');

		$this->assertResponseOk();
		$this->assertResponseContains('No potential duplicates found');
	}

	/**
	 * @return void
	 */
	public function testOrphaned(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'slug' => 'orphan',
			'label' => 'Orphan',
		]));

		$this->get('/admin/tags/tags/orphaned');

		$this->assertResponseOk();
		$this->assertResponseContains('Orphan');
	}

	/**
	 * @return void
	 */
	public function testOrphanedEmpty(): void {
		$this->get('/admin/tags/tags/orphaned');

		$this->assertResponseOk();
		$this->assertResponseContains('No orphaned tags found');
	}

	/**
	 * @return void
	 */
	public function testDeleteOrphaned(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'slug' => 'orphan-1',
			'label' => 'Orphan 1',
		]));
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'slug' => 'orphan-2',
			'label' => 'Orphan 2',
		]));

		$this->post('/admin/tags/tags/delete-orphaned');

		$this->assertRedirect('/admin/tags/tags/orphaned');
		$this->assertFlashMessage(__d('tags', '{0} orphaned tags have been deleted.', 2));
	}

	/**
	 * @return void
	 */
	public function testDeleteOrphanedNoneFound(): void {
		$this->post('/admin/tags/tags/delete-orphaned');

		$this->assertRedirect('/admin/tags/tags/orphaned');
		$this->assertFlashMessage(__d('tags', 'No orphaned tags found.'));
	}

	/**
	 * @return void
	 */
	public function testDeleteOrphanedRejectsGet(): void {
		$this->expectException(MethodNotAllowedException::class);

		$this->get('/admin/tags/tags/delete-orphaned');
	}

	/**
	 * @return void
	 */
	public function testRecalculateCounters(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		// Manually mess up the counter
		$tagsTable->updateAll(['counter' => 99], ['id' => 1]);

		$this->post('/admin/tags/tags/recalculate-counters');

		$this->assertRedirect('/admin/tags');

		$tag = $tagsTable->get(1);
		$this->assertSame(3, $tag->counter);
	}

	/**
	 * @return void
	 */
	public function testRecalculateCountersAlreadyCorrect(): void {
		$this->post('/admin/tags/tags/recalculate-counters');

		$this->assertRedirect('/admin/tags');
		$this->assertFlashMessage(__d('tags', 'All counters are already correct.'));
	}

	/**
	 * @return void
	 */
	public function testRecalculateCountersRejectsGet(): void {
		$this->expectException(MethodNotAllowedException::class);

		$this->get('/admin/tags/tags/recalculate-counters');
	}

	/**
	 * @return void
	 */
	public function testExport(): void {
		$this->get('/admin/tags/tags/export');

		$this->assertResponseOk();
		$this->assertContentType('csv');
		$this->assertHeaderContains('Content-Disposition', 'attachment;');
		$this->assertHeaderContains('Content-Disposition', '.csv');
		$this->assertResponseContains('id,namespace,slug,label,color,counter,created,modified');
		$this->assertResponseContains('color,Color,#FF5733,3');
		$this->assertResponseContains('dark-color,"Dark Color"');
	}

	/**
	 * @return void
	 */
	public function testExportFilteredByNamespace(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'namespace' => 'palette',
			'slug' => 'palette-only',
			'label' => 'Palette Only',
		]));

		$this->get('/admin/tags/tags/export?namespace=palette');

		$this->assertResponseOk();
		$this->assertContentType('csv');
		$this->assertResponseContains('palette-only');
		$this->assertResponseNotContains('dark-color');
	}

	/**
	 * @return void
	 */
	public function testExportFilteredByEmptyNamespace(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'namespace' => 'palette',
			'slug' => 'palette-only',
			'label' => 'Palette Only',
		]));

		$this->get('/admin/tags/tags/export?namespace=');

		$this->assertResponseOk();
		$this->assertResponseContains('color,Color');
		$this->assertResponseNotContains('palette-only');
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
	public function testChangeNamespaceMovesTagsToNewNamespace(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');

		$this->post('/admin/tags/tags/change-namespace', [
			'from_namespace' => '__none__',
			'to_namespace_select' => '',
			'to_namespace_new' => 'brand-new',
		]);

		$this->assertRedirect('/admin/tags/tags/change-namespace');

		$result = $tagsTable->find()
			->where(['namespace' => 'brand-new'])
			->all()
			->count();
		$this->assertSame(2, $result);
	}

	/**
	 * @return void
	 */
	public function testChangeNamespaceSameSourceAndTargetShowsError(): void {
		$this->post('/admin/tags/tags/change-namespace', [
			'from_namespace' => '__none__',
			'to_namespace_select' => '__none__',
			'to_namespace_new' => '',
		]);

		$this->assertResponseOk();
		$this->assertNoRedirect();
		$this->assertFlashMessage(__d('tags', 'Source and target namespaces must be different.'));
	}

	/**
	 * @return void
	 */
	public function testChangeNamespaceWithNoSourceTagsShowsInfo(): void {
		$this->post('/admin/tags/tags/change-namespace', [
			'from_namespace' => 'does-not-exist',
			'to_namespace_select' => 'palette',
			'to_namespace_new' => '',
		]);

		$this->assertResponseOk();
		$this->assertNoRedirect();
		$this->assertFlashMessage(__d('tags', 'No tags found in the source namespace.'));
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

}
