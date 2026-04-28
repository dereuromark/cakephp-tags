<?php
declare(strict_types=1);

namespace Tags\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class TagsDashboardControllerTest extends TestCase {

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
	}

	/**
	 * @return void
	 */
	public function testIndex(): void {
		$this->get('/admin/tags');

		$this->assertResponseOk();
		$this->assertResponseContains('Dashboard');
		$this->assertResponseContains('Total Tags');
		$this->assertResponseContains('Most Used Tags');
		$this->assertResponseContains('Tags by Namespace');
		$this->assertResponseContains('Models Using Tags');
		$this->assertResponseContains('Recently Created Tags');
	}

	/**
	 * @return void
	 */
	public function testIndexRendersStats(): void {
		$this->get('/admin/tags');

		$this->assertResponseOk();
		// Two tag fixture rows
		$this->assertResponseContains('Color');
		$this->assertResponseContains('Dark Color');
		// 5 tagged fixture rows across Muffins/Buns
		$this->assertResponseContains('Muffins');
		$this->assertResponseContains('Buns');
	}

	/**
	 * @return void
	 */
	public function testIndexWithOrphans(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'slug' => 'unused',
			'label' => 'Unused',
		]));

		$this->get('/admin/tags');

		$this->assertResponseOk();
		$this->assertResponseContains('Orphaned Tags');
		$this->assertResponseContains('Unused');
	}

	/**
	 * @return void
	 */
	public function testIndexWithDuplicates(): void {
		$tagsTable = TableRegistry::getTableLocator()->get('Tags.Tags');
		// Make sure there's a duplicate group ("color" / "colors")
		$tagsTable->saveOrFail($tagsTable->newEntity([
			'slug' => 'colors',
			'label' => 'Colors',
		]));

		$this->get('/admin/tags');

		$this->assertResponseOk();
		$this->assertResponseContains('Duplicates');
	}

}
