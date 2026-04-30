<?php
declare(strict_types=1);

namespace Tags\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use RuntimeException;

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
		Configure::delete('Tags.accessCheck');
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

	/**
	 * Without an accessCheck configured, the gate is a no-op (host
	 * AppController auth alone applies). Default behavior, no breaking change.
	 *
	 * @return void
	 */
	public function testAccessCheckUnsetIsNoOp(): void {
		$this->get('/admin/tags/tags');

		$this->assertResponseOk();
	}

	/**
	 * A non-Closure value is rejected immediately so a misconfigured key does
	 * not silently allow access.
	 *
	 * @return void
	 */
	public function testAccessCheckNonClosureRejects(): void {
		Configure::write('Tags.accessCheck', 'not a closure');

		$this->expectException(ForbiddenException::class);
		$this->get('/admin/tags/tags');
	}

	/**
	 * @return void
	 */
	public function testAccessCheckClosureFalseRejects(): void {
		Configure::write('Tags.accessCheck', fn () => false);

		$this->expectException(ForbiddenException::class);
		$this->get('/admin/tags/tags');
	}

	/**
	 * Truthy non-bool returns are rejected (no coercion).
	 *
	 * @return void
	 */
	public function testAccessCheckRequiresStrictTrue(): void {
		Configure::write('Tags.accessCheck', fn () => 1);

		$this->expectException(ForbiddenException::class);
		$this->get('/admin/tags/tags');
	}

	/**
	 * @return void
	 */
	public function testAccessCheckClosureTrueAllows(): void {
		Configure::write('Tags.accessCheck', fn () => true);

		$this->get('/admin/tags/tags');

		$this->assertResponseOk();
	}

	/**
	 * A throwing Closure is converted to 403 (the original exception is
	 * logged, the client sees a generic forbidden).
	 *
	 * @return void
	 */
	public function testAccessCheckThrowingYields403(): void {
		Configure::write('Tags.accessCheck', function (): bool {
			throw new RuntimeException('oops');
		});

		$this->expectException(ForbiddenException::class);
		$this->get('/admin/tags/tags');
	}

	/**
	 * A Closure that itself throws ForbiddenException is respected as-is so
	 * callers can short-circuit with their own message.
	 *
	 * @return void
	 */
	public function testAccessCheckExplicitForbiddenIsRespected(): void {
		Configure::write('Tags.accessCheck', function (): bool {
			throw new ForbiddenException('custom denial reason');
		});

		$this->expectException(ForbiddenException::class);
		$this->expectExceptionMessage('custom denial reason');
		$this->get('/admin/tags/tags');
	}

	/**
	 * The Closure receives the request, so callers can inspect path/identity/etc.
	 *
	 * @return void
	 */
	public function testAccessCheckReceivesRequest(): void {
		$received = null;
		Configure::write('Tags.accessCheck', function ($request) use (&$received): bool {
			$received = $request;

			return true;
		});

		$this->get('/admin/tags/tags');

		$this->assertResponseOk();
		$this->assertNotNull($received);
		$this->assertStringContainsString('tags', $received->getPath());
	}

}
