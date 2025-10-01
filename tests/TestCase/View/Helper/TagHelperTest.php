<?php

namespace Tags\Test\TestCase\View\Helper;

use Cake\Http\Response;
use Cake\Http\ServerRequest as Request;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Tags\View\Helper\TagHelper;

class TagHelperTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected array $fixtures = [
		'plugin.Tags.Tagged',
	];

	/**
	 * @var \Cake\View\View
	 */
	protected $View;

	/**
	 * @var \Tags\View\Helper\TagHelper
	 */
	protected $TagHelper;

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
		$this->TagHelper = new TagHelper($this->View);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		unset($this->TagHelper);
	}

	/**
	 * @return void
	 */
	public function testControlStringEmpty() {
		$result = $this->TagHelper->control();

		$expected = '<div class="input text"><label for="tag-list">Tags</label><input type="text" name="tag_list" id="tag-list"></div>';
		$this->assertSame($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testControlString() {
		$entity = TableRegistry::getTableLocator()->get('Tags.Tagged')->newEmptyEntity();
		$entity->tag_list = 'One, Two';

		$this->TagHelper->Form->create($entity);

		$result = $this->TagHelper->control();
		$expected = <<<HTML
<div class="input text">
	<label for="tag-list">Tags</label>
	<input type="text" name="tag_list" id="tag-list" value="One, Two">
</div>
HTML;
		$expected = str_replace(["\t", "\n", "\r"], '', $expected);
		$this->assertTextEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testControlArrayEmpty() {
		$this->TagHelper->setConfig('strategy', 'array');

		$result = $this->TagHelper->control();

		$expected = '<div class="input select"><label for="tag-list">Tags</label><input type="hidden" name="tag_list" value=""><select name="tag_list[]" multiple="multiple" id="tag-list"></select></div>';
		$this->assertSame($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testControlArray() {
		$this->TagHelper->setConfig('strategy', 'array');

		$entity = TableRegistry::getTableLocator()->get('Tags.Tagged')->newEmptyEntity();
		$entity->tag_list = [
			'One', 'Two',
		];
		$this->TagHelper->Form->create($entity);

		$result = $this->TagHelper->control();
		$expected = <<<HTML
<div class="input select">
	<label for="tag-list">Tags</label>
	<input type="hidden" name="tag_list" value="">
	<select name="tag_list[]" multiple="multiple" id="tag-list">
		<option value="One" selected="selected">One</option>
		<option value="Two" selected="selected">Two</option>
	</select>
</div>
HTML;
		$expected = str_replace(["\t", "\n", "\r"], '', $expected);
		$this->assertTextEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testGetContrastColor() {
		$this->assertSame('#000000', $this->TagHelper->getContrastColor('#FFFFFF'));
		$this->assertSame('#ffffff', $this->TagHelper->getContrastColor('#000000'));
		$this->assertSame('#000000', $this->TagHelper->getContrastColor('#FFFF00'));
		$this->assertSame('#ffffff', $this->TagHelper->getContrastColor('#0000FF'));
		$this->assertSame('#ffffff', $this->TagHelper->getContrastColor('#FF5733'));
	}

	/**
	 * @return void
	 */
	public function testTag() {
		$tag = ['label' => 'PHP', 'color' => '#787CB5'];
		$result = $this->TagHelper->tag($tag);

		$this->assertStringContainsString('PHP', $result);
		$this->assertStringContainsString('background-color: #787CB5', $result);
		$this->assertStringContainsString('color: #ffffff', $result);
		$this->assertStringContainsString('<span', $result);
	}

	/**
	 * @return void
	 */
	public function testTagWithDefaultColor() {
		$tag = ['label' => 'Default'];
		$result = $this->TagHelper->tag($tag);

		$this->assertStringContainsString('Default', $result);
		$this->assertStringContainsString('background-color: #cccccc', $result);
		$this->assertStringContainsString('color: #000000', $result);
	}

	/**
	 * @return void
	 */
	public function testTagWithUrl() {
		$tag = ['label' => 'PHP', 'color' => '#787CB5'];
		$result = $this->TagHelper->tag($tag, ['url' => '/tags/view/php']);

		$this->assertStringContainsString('<a href="/tags/view/php"', $result);
		$this->assertStringContainsString('PHP', $result);
		$this->assertStringContainsString('background-color: #787CB5', $result);
	}

	/**
	 * @return void
	 */
	public function testTagWithCustomOptions() {
		$tag = ['label' => 'Test'];
		$result = $this->TagHelper->tag($tag, [
			'defaultColor' => '#FF0000',
			'class' => 'custom-tag',
		]);

		$this->assertStringContainsString('background-color: #FF0000', $result);
		$this->assertStringContainsString('class="custom-tag"', $result);
	}

	/**
	 * @return void
	 */
	public function testTags() {
		$tags = [
			['label' => 'PHP', 'color' => '#787CB5'],
			['label' => 'JavaScript', 'color' => '#F0DB4F'],
		];
		$result = $this->TagHelper->tags($tags);

		$this->assertStringContainsString('PHP', $result);
		$this->assertStringContainsString('JavaScript', $result);
		$this->assertStringContainsString('<div class="tags-container">', $result);
	}

	/**
	 * @return void
	 */
	public function testTagsEmpty() {
		$result = $this->TagHelper->tags([]);

		$this->assertSame('', $result);
	}

	/**
	 * @return void
	 */
	public function testTagsWithoutWrapper() {
		$tags = [
			['label' => 'PHP', 'color' => '#787CB5'],
		];
		$result = $this->TagHelper->tags($tags, ['wrapper' => false]);

		$this->assertStringContainsString('PHP', $result);
		$this->assertStringNotContainsString('<div', $result);
	}

	/**
	 * @return void
	 */
	public function testColorControl() {
		$result = $this->TagHelper->colorControl();

		$this->assertStringContainsString('type="color"', $result);
		$this->assertStringContainsString('name="color"', $result);
	}

	/**
	 * @return void
	 */
	public function testBadges() {
		$tags = [
			['label' => 'PHP', 'color' => '#787CB5'],
			['label' => 'JavaScript', 'color' => '#F0DB4F'],
		];
		$result = $this->TagHelper->badges($tags);

		$this->assertStringContainsString('PHP', $result);
		$this->assertStringContainsString('class="badge"', $result);
		$this->assertStringContainsString('border-radius: 0.25rem', $result);
	}

	/**
	 * @return void
	 */
	public function testPills() {
		$tags = [
			['label' => 'PHP', 'color' => '#787CB5'],
		];
		$result = $this->TagHelper->pills($tags);

		$this->assertStringContainsString('PHP', $result);
		$this->assertStringContainsString('class="badge rounded-pill"', $result);
		$this->assertStringContainsString('border-radius: 10rem', $result);
	}

	/**
	 * @return void
	 */
	public function testAdjustBrightness() {
		$color = '#808080';

		$lighter = $this->TagHelper->adjustBrightness($color, 20);
		$this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $lighter);
		$this->assertNotSame($color, $lighter);

		$darker = $this->TagHelper->adjustBrightness($color, -20);
		$this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $darker);
		$this->assertNotSame($color, $darker);

		$this->assertNotSame($lighter, $darker);
	}

	/**
	 * @return void
	 */
	public function testAdjustBrightnessLimits() {
		$white = $this->TagHelper->adjustBrightness('#FFFFFF', 50);
		$this->assertSame('#FFFFFF', $white);

		$black = $this->TagHelper->adjustBrightness('#000000', -50);
		$this->assertSame('#000000', $black);
	}

	/**
	 * @return void
	 */
	public function testList() {
		$tags = [
			['label' => 'PHP', 'color' => '#787CB5', 'slug' => 'php'],
			['label' => 'JavaScript', 'color' => '#F0DB4F', 'slug' => 'javascript'],
		];
		$result = $this->TagHelper->list($tags);

		$this->assertStringContainsString('PHP', $result);
		$this->assertStringContainsString('JavaScript', $result);
	}

	/**
	 * @return void
	 */
	public function testListWithUrl() {
		$tags = [
			['label' => 'PHP', 'color' => '#787CB5', 'slug' => 'php'],
		];
		$result = $this->TagHelper->list($tags, ['url' => '/tags/view']);

		$this->assertStringContainsString('<a href="/tags/view/php"', $result);
	}

	/**
	 * @return void
	 */
	public function testListWithSeparator() {
		$tags = [
			['label' => 'PHP', 'color' => '#787CB5', 'slug' => 'php'],
			['label' => 'JavaScript', 'color' => '#F0DB4F', 'slug' => 'javascript'],
		];
		$result = $this->TagHelper->list($tags, ['separator' => ', ']);

		$this->assertStringContainsString('PHP', $result);
		$this->assertStringContainsString(', ', $result);
		$this->assertStringContainsString('JavaScript', $result);
	}

}
