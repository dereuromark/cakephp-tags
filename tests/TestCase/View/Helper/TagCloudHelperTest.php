<?php

namespace Tags\Test\TestCase\View\Helper;

use Cake\Http\Response;
use Cake\Http\ServerRequest as Request;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Tags\View\Helper\TagCloudHelper;

class TagCloudHelperTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected array $fixtures = [
		'plugin.Tags.Tagged',
		'plugin.Tags.Tags',
	];

	/**
	 * @var \Cake\View\View
	 */
	protected $View;

	/**
	 * @var \Tags\View\Helper\TagCloudHelper
	 */
	protected $Helper;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$request = new Request();
		$response = new Response();
		$this->View = new View($request, $response);
		$this->Helper = new TagCloudHelper($this->View);

		// Needed only for fake requests (tests)
		$this->Helper->setConfig('url', ['controller' => 'MyController', 'action' => 'index']);

		$builder = Router::createRouteBuilder('/');
		$builder->scope('/', function (RouteBuilder $routes): void {
			$routes->fallbacks(DashedRoute::class);
		});
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		unset($this->Helper);
	}

	/**
	 * @return void
	 */
	public function testDisplayUlLi() {
		$tags = [
			[
				'id' => 1,
				'weight' => 12,
				'counter' => 2,
				'tag' => [
					'id' => 1,
					'label' => 'Foo',
					'slug' => 'Foo',
				],
			],
			[
				'id' => 2,
				'weight' => 20,
				'counter' => 4,
				'tag' => [
					'id' => 2,
					'label' => 'Bar',
					'slug' => 'Bar',
				],
			],
			[
				'id' => 3,
				'weight' => 8,
				'counter' => 3,
				'tag' => [
					'id' => 3,
					'label' => 'X Y Z',
					'slug' => 'X-Y-Z',
				],
			],
		];
		$options = [
			'shuffle' => false,
		];

		// Wrap with <ul class="tag-cloud">...</ul>
		$result = $this->Helper->display($tags, $options, ['class' => 'tag-cloud']);

		$expected = <<<HTML
<ul class="tag-cloud">
<li style="font-size: 80%"><a href="/my-controller?by=Foo" id="tag-1">Foo</a></li>
<li style="font-size: 160%"><a href="/my-controller?by=Bar" id="tag-2">Bar</a></li>
<li style="font-size: 120%"><a href="/my-controller?by=X-Y-Z" id="tag-3">X Y Z</a></li>
</ul>
HTML;
		$expected = str_replace(["\t", "\n", "\r"], '', $expected);
		$result = str_replace(["\t", "\n", "\r"], '', $result);
		$this->assertTextEquals($expected, $result);
	}

}
