<?php
declare(strict_types=1);

namespace Tags\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class TagsAppControllerTest extends TestCase {

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
	public function tearDown(): void {
		parent::tearDown();

		Configure::delete('Tags.standalone');
		Configure::delete('Tags.adminLayout');
	}

	/**
	 * @return void
	 */
	public function testDefaultLayoutUsesPluginLayout(): void {
		$this->get('/admin/tags/tags');

		$this->assertResponseOk();
		$this->assertResponseContains('Tags Admin');
		$this->assertResponseContains('Bootstrap');
	}

	/**
	 * @return void
	 */
	public function testCustomLayoutFromConfig(): void {
		Configure::write('Tags.adminLayout', 'Tags.tags');

		$this->get('/admin/tags/tags');

		$this->assertResponseOk();
		$this->assertResponseContains('Tags Admin');
	}

	/**
	 * @return void
	 */
	public function testStandaloneMode(): void {
		Configure::write('Tags.standalone', true);

		$this->get('/admin/tags/tags');

		$this->assertResponseOk();
	}

}
