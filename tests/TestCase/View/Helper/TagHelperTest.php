<?php
namespace Tags\Test\TestCase\View\Helper;

use Cake\Http\ServerRequest as Request;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\Stub\Response;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Tags\View\Helper\TagHelper;

class TagHelperTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.tags.tagged',
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
	public function setUp() {
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
	public function tearDown() {
		parent::tearDown();
		unset($this->TagHelper);
	}

	/**
	 * @return void
	 */
	public function testControl() {
		$result = $this->TagHelper->control();

		$expected = '<div class="input text"><label for="tag-list">Tags</label><input type="text" name="tag_list" id="tag-list"/></div>';
		$this->assertSame($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testControlArray() {
		$this->TagHelper->setConfig('strategy', 'array');
		$result = $this->TagHelper->control();

		// Empty one
		$expected = '<div class="input select"><label for="tag-list">Tags</label><input type="hidden" name="tag_list" value=""/><select name="tag_list[]" multiple="multiple" id="tag-list"></select></div>';
		$this->assertSame($expected, $result);

		$entity = TableRegistry::get('Tags.Tagged')->newEntity();
		$entity->tag_list = [
			'One', 'Two'
		];
		$this->TagHelper->Form->create($entity);

		$result = $this->TagHelper->control();
		$expected = <<<HTML
<div class="input select">
	<label for="tag-list">Tags</label>
	<input type="hidden" name="tag_list" value=""/>
	<select name="tag_list[]" multiple="multiple" id="tag-list">
		<option value="One" selected="selected">One</option>
		<option value="Two" selected="selected">Two</option>
	</select>
</div>
HTML;
		$expected = str_replace(["\t", "\n", "\r"], '', $expected);
		$this->assertTextEquals($expected, $result);
	}

}
